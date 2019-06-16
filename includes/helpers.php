<?php
/**
 * helpers.php
 *
 * Functions available in both Laravel and Legacy code (must not call any other legacy functions)
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Util\Laravel;

if (!function_exists('d_echo')) {
    /**
     * Legacy convenience function - please use this instead of 'if ($debug) { echo ...; }'
     * Use Log directly in pure Laravel code!
     *
     * @param string|array $text The error message or array to print
     * @param string $no_debug_text Text to print if debug is disabled
     */
    function d_echo($text, $no_debug_text = null)
    {
        global $debug;

        if (Laravel::isBooted()) {
            \Log::debug(is_string($text) ? rtrim($text) : $text);
        } elseif ($debug) {
            print_r($text);
        }

        if (!$debug && $no_debug_text) {
            echo "$no_debug_text";
        }
    }
}

if (!function_exists('set_debug')) {
    /**
     * Set debugging output
     *
     * @param bool $state If debug is enabled or not
     * @param bool $silence When not debugging, silence every php error
     * @return bool
     */
    function set_debug($state = true, $silence = false)
    {
        global $debug;

        $debug = $state; // set to global

        restore_error_handler(); // disable Laravel error handler

        if ($debug) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            ini_set('log_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE);

            \LibreNMS\Util\Laravel::enableCliDebugOutput();
            \LibreNMS\Util\Laravel::enableQueryDebug();
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            ini_set('log_errors', 1);
            error_reporting($silence ? 0 : E_ERROR);

            \LibreNMS\Util\Laravel::disableCliDebugOutput();
            \LibreNMS\Util\Laravel::disableQueryDebug();
        }

        return $debug;
    }
}

if (!function_exists('array_pairs')) {
    /**
     * Get all consecutive pairs of values in an array.
     * [1,2,3,4] -> [[1,2],[2,3],[3,4]]
     *
     * @param array $array
     * @return array
     */
    function array_pairs($array)
    {
        $pairs = [];

        for ($i = 1; $i < count($array); $i++) {
            $pairs[] = [$array[$i - 1], $array[$i]];
        }

        return $pairs;
    }
}
