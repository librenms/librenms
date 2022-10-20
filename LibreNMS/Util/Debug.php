<?php
/*
 * Debug.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App;
use Log;

class Debug
{
    /** @var bool */
    private static $debug = false;
    /** @var bool */
    private static $verbose = false;
    /** @var bool */
    private static $sql_debug_enabled = false;

    /**
     * Enable/disable debug output
     *
     * @param  bool  $debug  whether to enable or disable debug output
     * @param  bool  $silence  Silence error output or output all errors except notices
     * @return bool returns $debug
     */
    public static function set($debug = true, bool $silence = false): bool
    {
        self::$debug = (bool) $debug;

        if (self::$debug) {
            self::enableErrorReporting();
            self::enableCliDebugOutput();
            self::enableQueryDebug();
        } else {
            self::disableErrorReporting($silence);
            self::disableCliDebugOutput($silence);
            self::disableQueryDebug();
        }

        return self::$debug;
    }

    /**
     * Set debug without configuring error reporting.
     */
    public static function setOnly(bool $debug = true): bool
    {
        return self::$debug = $debug;
    }

    public static function setVerbose(bool $verbose = true): void
    {
        self::$verbose = $verbose;
    }

    public static function isEnabled(): bool
    {
        return self::$debug;
    }

    public static function isVerbose(): bool
    {
        return self::$verbose;
    }

    public static function disableQueryDebug(): void
    {
        self::$sql_debug_enabled = false;
    }

    public static function enableCliDebugOutput(): void
    {
        if (Laravel::isBooted() && App::runningInConsole()) {
            Log::setDefaultDriver('console_debug');
        } else {
            putenv('LOG_CHANNEL=console_debug');
        }
    }

    public static function disableCliDebugOutput(bool $silence): void
    {
        if (Laravel::isBooted() && Log::getDefaultDriver() !== 'stack') {
            Log::setDefaultDriver(app()->runningInConsole() && ! $silence ? 'console' : 'stack');
        }
    }

    public static function enableQueryDebug(): void
    {
        self::$sql_debug_enabled = true;
    }

    public static function queryDebugIsEnabled(): bool
    {
        return self::$sql_debug_enabled;
    }

    /**
     * Disable error reporting, do not use with new code
     */
    public static function disableErrorReporting(bool $silence = false): void
    {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        error_reporting($silence ? 0 : E_ERROR);
    }

    /**
     * Enable error reporting. Please call after disabling for legacy code
     */
    public static function enableErrorReporting(): void
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        ini_set('log_errors', '0');
        error_reporting(E_ALL & ~E_NOTICE);
    }
}
