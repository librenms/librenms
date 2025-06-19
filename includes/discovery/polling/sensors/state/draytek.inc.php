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
$ltestatus_lookup_table = [
    'Detecting' => 0,
    'Initialization' => 1,
    'SIM card ready' => 2,
    'SMS service ready' => 3,
    'Search Network' => 4,
    'Registration denied' => 5,
    'Bridged' => 6,
];

if ($sensor['sensor_type'] === 'ltestatus') {
    $sensor_value = $ltestatus_lookup_table[$sensor_value];
}
