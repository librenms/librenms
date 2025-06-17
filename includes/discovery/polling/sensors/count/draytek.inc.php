<?php

/**
 * draytek.php
 *
 * DrayTek OS
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
 * @copyright  2025 CTNET BV
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */
if ($sensor['sensor_type'] === 'ltemaxchannelratetx' || $sensor['sensor_type'] === 'ltemaxchannelraterx') {
    $sensor_value = preg_replace('/\D/', '', $sensor_value);        // Remove any non-numeric characters

    // If non-numeric value, value is zero (eg: "--- Mbps" which the preg_replace replaces to "")
    if ($sensor_value == "") {
        $sensor_value = 0;
    }
}
