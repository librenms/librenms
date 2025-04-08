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
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use LibreNMS\Util\Git;
use Spatie\LaravelIgnition\Facades\Flare;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorReporting
{
    private ?bool $reportingEnabled = null;
    private ?bool $dumpErrors = null;
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
        Flare::determineVersionUsing(function () {
            return \LibreNMS\Util\Version::VERSION;
        });

        $exceptions->dontReportDuplicates();
        $exceptions->throttle(function (Throwable $e) {
            return Limit::perMinute(LibrenmsConfig::get('reporting.throttle', 30));
        });

        app()->booted(function () {
            $this->dumpErrors = LibrenmsConfig::get('reporting.dump_errors', false);
            if ($this->dumpErrors) {
                config([
                    'logging.deprecations.channel' => 'deprecations_channel',
                    'logging.deprecations.trace' => true,
                ]);
            }
        });

        // handle exceptions
        $exceptions->report([$this, 'report']);
        $exceptions->render([$this, 'render']);
    }

    public function report(Throwable $e): bool
    {
        if ($this->dumpErrors) {
            \Log::critical('%RException: ' . get_class($e) . ' ' . $e->getMessage() . '%n @ %G' . $e->getFile() . ':' . $e->getLine() . '%n' . PHP_EOL . $e->getTraceAsString(), ['color' => true]);
        }

        if ($this->isReportingEnabled()) {
            Flare::report($e);
        }

        // block logging errors if in a legacy entry point (init.php)
        return ! defined('IGNORE_ERRORS');
    }

    public function render(Throwable $exception, Request $request): ?Response
    {
        // TODO FIX ME!
        // TODO FIX ME!
        // TODO FIX ME!
        // TODO FIX ME!
        // TODO FIX ME!
        if (
            $exception instanceof \Illuminate\Auth\AuthenticationException ||
            $exception instanceof \Illuminate\Validation\ValidationException
        ) {
            return null;
        }
        dd($exception);
        // TODO FIX ME!
        // TODO FIX ME!
        // TODO FIX ME!
        // TODO FIX ME!
        // TODO FIX ME!

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

            // debug is not enabled, render a more helpful error
            if (! $exception instanceof HttpExceptionInterface) {
                return response()->view('errors.generic', ['content' => 'Server Error: Set APP_DEBUG=true to see details or check the librenms.log file']);
            }
        }

        return null; // use default rendering
    }

    /**
     * Checks the state of the config and current install to determine if reporting should be enabled
     * The primary factor is the setting reporting.error
     */
    public function isReportingEnabled(): bool
    {
        if ($this->reportingEnabled !== null) {
            return $this->reportingEnabled;
        }

        // safety check so we don't leak early reports (but reporting should not be loaded before the config is)
        if (! app()->bound('librenms-config')) {
            return false;
        }

        $this->reportingEnabled = false; // don't cache before config is loaded

        // check the user setting
        if (LibrenmsConfig::get('reporting.error') !== true) {
            \Log::debug('Reporting disabled by user setting');

            return false;
        }

        // Only run in production
        if (! app()->isProduction()) {
            \Log::debug('Reporting disabled because app is not in production');

            return false;
        }

        // Check git
        $git = Git::make(180);
        if ($git->isAvailable()) {
            if (! Str::contains($git->remoteUrl(), ['git@github.com:librenms/librenms.git', 'https://github.com/librenms/librenms.git'])) {
                \Log::debug('Reporting disabled because LibreNMS is not from the official repository');

                return false;
            }

            if ($git->hasChanges()) {
                \Log::debug('Reporting disabled because LibreNMS is not from the official repository');

                return false;
            }

            if (! $git->isOfficialCommit()) {
                \Log::debug('Reporting disabled due to local modifications');

                return false;
            }
        }

        $this->reportingEnabled = true;

        return true;
    }
}
