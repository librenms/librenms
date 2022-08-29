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

use App\Logging\Reporting\Middleware\CleanContext;
use App\Logging\Reporting\Middleware\SetGroups;
use ErrorException;
use Facade\FlareClient\Report;
use Facade\Ignition\Facades\Flare;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Util\Git;

class ErrorReportingProvider extends \Facade\Ignition\IgnitionServiceProvider
{
    /** @var int */
    protected $errorReportingLevel = E_ALL & ~E_NOTICE;
    /** @var callable */
    private $laravelErrorHandler;
    /** @var bool */
    private $reportingEnabled;

    public function boot(): void
    {
        Flare::filterExceptionsUsing(function (\Exception $e) {
            return $this->isReportingEnabled();
        });

        Flare::filterReportsUsing(function (Report $report) {
            return $this->isReportingEnabled();
        });

        Flare::determineVersionUsing(function () {
            return \LibreNMS\Util\Version::VERSION;
        });

        // Filter some extra fields for privacy
        // Move to header middleware when switching to spatie/laravel-ignition
        Flare::registerMiddleware(CleanContext::class);

        // Add more LibreNMS related info
        Flare::registerMiddleware(SetGroups::class);

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
        if (! Config::get('reporting.error')) {
            \Log::debug('Reporting disabled by user setting');

            return false;
        }

        // Only run in production
        if (! $this->app->isProduction()) {
            \Log::debug('Reporting disabled because app is not in production');

            return false;
        }

        // Check git
        if (Git::repoPresent()) {
            if (! Str::contains(Git::remoteUrl(), ['git@github.com:librenms/librenms.git', 'https://github.com/librenms/librenms.git'])) {
                \Log::debug('Reporting disabled because LibreNMS is not from the official repository');

                return false;
            }

            if (! Git::unchanged()) {
                \Log::debug('Reporting disabled because LibreNMS is not from the official repository');

                return false;
            }

            if (! Git::officalCommit()) {
                \Log::debug('Reporting disabled due to local modifications');

                return false;
            }
        }

        $this->reportingEnabled = true;

        return true;
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
}
