<?php

/**
 * ErrorReporting.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Exceptions;

use App\Facades\LibrenmsConfig;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Throwable;

class ErrorReporting
{
    private const MAX_PROD_ERRORS = 4;
    private int $errorCount = 0;
    protected array $upgradable = [
        \LibreNMS\Exceptions\FilePermissionsException::class,
        \LibreNMS\Exceptions\DatabaseConnectException::class,
        \LibreNMS\Exceptions\DuskUnsafeException::class,
        \LibreNMS\Exceptions\UnserializableRouteCache::class,
        \LibreNMS\Exceptions\MaximumExecutionTimeExceeded::class,
        \LibreNMS\Exceptions\DatabaseInconsistentException::class,
    ];

    public function __construct(Exceptions $exceptions)
    {
        $this->adjustErrorHandlingForAppEnv(app()->environment());

        $exceptions->dontReportDuplicates();
        $exceptions->throttle(function (Throwable $e): Limit {
            $limit = (int) LibrenmsConfig::get('reporting.throttle', 30);

            return $limit > 0 ? Limit::perMinute($limit) : Limit::none();
        });
        $exceptions->reportable($this->reportable(...));
        $exceptions->render($this->render(...));
    }

    public function reportable(Throwable $e): bool
    {
        \Log::critical('%RException: ' . $e::class . ' ' . $e->getMessage() . '%n @ %G' . $e->getFile() . ':' . $e->getLine() . '%n' . PHP_EOL . $e->getTraceAsString(), ['color' => true]);

        return false; // false = block default log message
    }

    public function render(Throwable $exception, Request $request): Response|JsonResponse|null
    {
        // try to upgrade generic exceptions to more specific ones
        if (! config('app.debug')) {
            if ($exception instanceof \Illuminate\View\ViewException || $exception instanceof \Spatie\LaravelIgnition\Exceptions\ViewException) {
                $base = $exception->getPrevious(); // get real exception
            }

            foreach ($this->upgradable as $class) {
                if ($new = $class::upgrade($base ?? $exception)) {
                    return $new->render($request);
                }
            }
        }

        return null; // use default rendering
    }

    private function adjustErrorHandlingForAppEnv(string $environment): void
    {
        // throw exceptions and deprecations in testing and non-prod when APP_DEBUG is set.
        if ($environment == 'testing' || ($environment !== 'production' && config('app.debug'))) {
            app()->booted(function (): void {
                config([
                    'logging.deprecations.channel' => 'deprecations_channel',
                    'logging.deprecations.trace' => true,
                ]);
            });

            return; // do not override error handler below
        }

        // in production, don't halt execution on non-fatal errors
        set_error_handler(function ($severity, $message, $file, $line) {
            // If file is from a package, find the first non-vendor frame
            if (self::isUndesirableTracePath($file)) {
                // add vendor file to message
                $message .= ' from ' . strstr($file, 'vendor') . ':' . $line;
                [$file, $line] = self::findFirstNonVendorFrame();
            }

            if ((error_reporting() & $severity) !== 0) { // this check primarily allows @ to suppress errors
                if ($this->errorCount++ < self::MAX_PROD_ERRORS) {
                    // limit reported errors so php-fpm headers don't get too large
                    $max_errors = $this->errorCount == self::MAX_PROD_ERRORS ? ' (max reported errors reached)' : '';

                    error_log("\e[31mPHP Error($severity)\e[0m: $message in $file:$line$max_errors");
                }
            }

            // For notices and warnings, prevent conversion to exceptions
            if (($severity & (E_NOTICE | E_WARNING | E_USER_NOTICE | E_USER_WARNING | E_DEPRECATED)) !== 0) {
                return true; // Prevent the standard error handler from running
            }

            return false; // For other errors, let Laravel handle them
        });
    }

    private static function findFirstNonVendorFrame(): array
    {
        foreach (debug_backtrace() as $trace) {
            // not vendor frames
            if (isset($trace['file']) && self::isUndesirableTracePath($trace['file'])) {
                continue;
            }
            // not this class
            if (isset($trace['class']) && $trace['class'] === self::class) {
                continue;
            }

            return [$trace['file'], $trace['line']];
        }

        return ['', ''];
    }

    private static function isUndesirableTracePath(string $path): bool
    {
        return Str::contains($path, [
            '/vendor/',
            '/storage/framework/views/',
        ]);
    }
}
