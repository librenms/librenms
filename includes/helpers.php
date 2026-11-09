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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Util\Debug;
use LibreNMS\Util\Laravel;

if (! function_exists('d_echo')) {
    /**
     * Legacy convenience function - please use this instead of 'if (Debug::isEnabled()) { echo ...; }'
     * Use Log directly in pure Laravel code!
     *
     * @param  string|array|float|int  $text  The error message or array to print
     * @param  string  $no_debug_text  Text to print if debug is disabled
     */
    function d_echo($text, $no_debug_text = null)
    {
        if (Laravel::isBooted()) {
            \Log::debug(is_string($text) ? rtrim($text) : $text);
        } elseif (Debug::isEnabled()) {
            print_r($text);
        }

        if (! Debug::isEnabled() && $no_debug_text) {
            echo "$no_debug_text";
        }
    }
}

if (! function_exists('toast')) {
    /**
     * send a toastr popup or return ToastInterface
     */
    function toast(?string $title = null, ?string $message = null, string $level = 'info', ?array $options = null)
    {
        $toast = app(\App\Http\Interfaces\ToastInterface::class);

        if (! is_null($message)) {
            return $toast->message($title, $message, $level, $options);
        }

        return $toast;
    }
}
