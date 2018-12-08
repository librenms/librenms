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

        if (class_exists('\Log')) {
            \Log::debug(is_string($text) ? rtrim($text) : $text);
        } elseif ($debug) {
            print_r($text);
        }

        if (!$debug && $no_debug_text) {
            echo "$no_debug_text";
        }
    }
}
