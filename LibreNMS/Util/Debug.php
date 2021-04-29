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

class Debug
{
    private static $debug = false;
    private static $verbose = false;

    public static function set(bool $debug = true, bool $silence = false): bool
    {
        self::$debug = $debug;

        restore_error_handler(); // disable Laravel error handler

        if (self::$debug) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            ini_set('log_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE);

            Laravel::enableCliDebugOutput();
            Laravel::enableQueryDebug();
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            ini_set('log_errors', 1);
            error_reporting($silence ? 0 : E_ERROR);

            Laravel::disableCliDebugOutput();
            Laravel::disableQueryDebug();
        }

        return self::$debug;
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
}
