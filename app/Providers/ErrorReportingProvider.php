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

use ErrorException;
use Facade\FlareClient\Report;
use Facade\Ignition\Facades\Flare;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Util\Git;
use LibreNMS\Util\Version;

class ErrorReportingProvider extends \Facade\Ignition\IgnitionServiceProvider
{
    protected $errorReportingLevel = E_ALL & ~E_NOTICE;
    private $laravelErrorHandler;

    public function boot()
    {
        // don't report when:
        Flare::filterExceptionsUsing(function (\Exception $e) {
            if (! Config::get('reporting.error')) {
                return false;
            }

            // Only run in production
            if (! $this->app->isProduction()) {
                return false;
            }

            // Check if git installation
            if (! Git::repoPresent()) {
                return false;
            }

            // Repo url must be offical one
            if (! Str::contains(Git::remoteUrl(), ['git@github.com:librenms/librenms.git', 'https://github.com/librenms/librenms.git'])) {
                return false;
            }

            // Check if repo is modified
            if (! Git::unchanged()) {
                return false;
            }

            // Check if repo is modified
            if (! Git::officalCommit()) {
                return false;
            }

            return true;
        });

        Flare::determineVersionUsing(function () {
            return \LibreNMS\Util\Version::VERSION;
        });

        Flare::registerMiddleware(function (Report $report, $next) {

            // Filter some extra fields for privacy
            // Move to header middleware when switching to spatie/laravel-ignition
            try {
                $report->setApplicationPath('');
                $context = $report->allContext();

                if (isset($context['request']['url'])) {
                    $context['request']['url'] = str_replace($context['headers']['host'] ?? '', 'librenms', $context['request']['url']);
                }

                if (isset($context['session']['_previous']['url'])) {
                    $context['session']['_previous']['url'] = str_replace($context['headers']['host'] ?? '', 'librenms', $context['session']['_previous']['url']);
                }

                $context['headers']['host'] = null;
                $context['headers']['referer'] = null;

                $report->userProvidedContext($context);
            } catch (\Exception $e) {
            }

            // Add more LibreNMS related info
            try {
                $version = Version::get();

                $report->group('LibreNMS', [
                    'Git version' => $version->local(),
                    'App version' => Version::VERSION,
                ]);

                $report->group('Tools', [
                    'Database' => $version->databaseServer(),
                    'Net-SNMP' => $version->netSnmp(),
                    'Python' => $version->python(),
                    'RRDtool' => $version->rrdtool(),

                ]);
            } catch (\Exception $e) {
            }

            return $next($report);
        });

        // Override the Laravel error handler
        $this->laravelErrorHandler = set_error_handler([$this, 'handleError']);

        parent::boot();
    }

    /**
     * Report PHP deprecations, or convert PHP errors to ErrorException instances.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        // report errors if they are allowed
        if ($this->errorReportingLevel & $level) {
            Flare::report(new ErrorException($message, 0, $level, $file, $line));
        }

        // call the laravel error handler, unless using a legacy entry point (init.php)
        if (! defined('IGNORE_ERRORS')) {
            call_user_func($this->laravelErrorHandler, $level, $message, $file, $line);
        }
    }
}
