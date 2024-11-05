<?php
/**
 * ErrorReportingProvider.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Providers;

use App\Logging\Reporting\Middleware\AddGitInformation;
use App\Logging\Reporting\Middleware\CleanContext;
use App\Logging\Reporting\Middleware\SetGroups;
use App\Logging\Reporting\Middleware\SetInstanceId;
use App\Models\Callback;
use ErrorException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Util\Git;
use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\Facades\Flare;

class ErrorReportingProvider extends \Spatie\LaravelIgnition\IgnitionServiceProvider
{
    /** @var int */
    protected $errorReportingLevel = E_ALL & ~E_NOTICE;
    /** @var callable */
    private $laravelErrorHandler;
    /** @var bool */
    private $reportingEnabled;
    /** @var string|null */
    private static $instanceId;

    private $throttle = 300;

    public function boot(): void
    {
        $this->throttle = Config::get('reporting.throttle', 300);

        /* @phpstan-ignore-next-line */
        if (! method_exists(\Spatie\FlareClient\Flare::class, 'filterReportsUsing')) {
            Log::debug("Flare client too old, disabling Ignition to avoid bug.\n");

            return;
        }

        Flare::filterExceptionsUsing(function (\Exception $e) {
            if (Config::get('reporting.dump_errors')) {
                \Log::critical('%RException: ' . get_class($e) . ' ' . $e->getMessage() . '%n @ %G' . $e->getFile() . ':' . $e->getLine() . '%n' . PHP_EOL . $e->getTraceAsString(), ['color' => true]);
            }

            // check if reporting is enabled and not throttled
            return $this->isReportingEnabled() && ! $this->isThrottled();
        });

        Flare::filterReportsUsing(function (Report $report) {
            return $this->isReportingEnabled();
        });

        Flare::determineVersionUsing(function () {
            return \LibreNMS\Util\Version::VERSION;
        });

        // add git information, but cache it unlike the upstream provider
        Flare::registerMiddleware(AddGitInformation::class);

        // Filter some extra fields for privacy
        // Move to header middleware when switching to spatie/laravel-ignition
        Flare::registerMiddleware(CleanContext::class);

        // Add more LibreNMS related info
        Flare::registerMiddleware(SetGroups::class);
        Flare::registerMiddleware(SetInstanceId::class);

        // Override the Laravel error handler but save it to call when in modern code
        $this->laravelErrorHandler = set_error_handler([$this, 'handleError']);

        parent::boot();
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
        if (! Config::isLoaded()) {
            return false;
        }

        $this->reportingEnabled = false; // don't cache before config is loaded

        // check the user setting
        if (Config::get('reporting.error') !== true) {
            \Log::debug('Reporting disabled by user setting');

            return false;
        }

        // Only run in production
        if (! $this->app->isProduction()) {
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

    private function isThrottled(): bool
    {
        if ($this->throttle) {
            $this->reportingEnabled = false; // disable future reporting (to avoid this cache check)

            try {
                if (Cache::get('recently_reported_error')) {
                    return true; // reporting is currently throttled
                }

                // let this report go and disable reporting for the next error
                Cache::put('recently_reported_error', true, $this->throttle);
            } catch (\Exception $e) {
                // ignore throttling exception (Such as database or redis not running for cache)
            }
        }

        return false;
    }

    /**
     * Report PHP deprecations, or convert PHP errors to ErrorException instances.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return bool
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = []): bool
    {
        // report errors if they are allowed
        if ($this->errorReportingLevel & $level) {
            Flare::report(new ErrorException($message, 0, $level, $file, $line));
        }

        // call the laravel error handler, unless using a legacy entry point (init.php)
        if (! defined('IGNORE_ERRORS')) {
            call_user_func($this->laravelErrorHandler, $level, $message, $file, $line);
        }

        return true;
    }

    public static function getInstanceId(): string
    {
        if (is_null(self::$instanceId)) {
            $uuid = Callback::get('error_reporting_uuid');

            if (! $uuid) {
                $uuid = Str::uuid();
                Callback::set('error_reporting_uuid', $uuid);
            }

            self::$instanceId = $uuid;
        }

        return self::$instanceId;
    }
}
