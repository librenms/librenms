<?php
/**
 * web600.inc.php
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
 * @copyright  2021 Beanfield Technologies Inc
 * @author     Jeremy Ouellet <jouellet@beanfield.com>
 */
$oid_base = '.1.3.6.1.4.1.8338.1.1.4.1.1.1.';

for ($x = 1; $x <= 9; $x++) {
    if ($x == 7) {
        // Sensor 7 is not a valid sensor.
        continue;
    }
    $oid = $oid_base . $x . '.53';
    $desc = $pre_cache['web600']['ioTable'][$x]['15'];
    $value = $pre_cache['web600']['ioTable'][$x]['53'];
    discover_sensor($valid['sensor'], 'count', $device, $oid, strval($x),
        $device['os'], $desc, 1, 1, null, null, null, null, $value);
}
