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

$types = [
    'ZERO',
    'no',
    'nc',
    'temp28kF',
    'temp28kC',
    'temp10kF',
    'temp10kC',
    '4-20ma',
    '0-5V',
    'water',
    'motion',
    'humidity',
    'unknown',
    'battery',
    'intPower',
    'sound',
    'intTemp',
    'lcd5V',
    'lcdContrast',
    'up',
    'down',
    'extPower',
    'unknown',
    'unknown',
    'unknown',
    'unknown',
    'unknown',
    'unknown',
    'unknown',
    'unknown',
    'none',
];

for ($x = 1; $x <= 9; $x++) {
    if ($x == 7) {
        // Sensor 7 is not a valid sensor.
        continue;
    }
    $entry = $pre_cache['web600-ioTable'][$x];
    $type = $types[$entry['18']];

    if (str_contains($type, 'temp') || str_contains($type, '-')) {
        $divisor = 100;
    } else {
        $divisor = 1;
    }

    $oid = $oid_base . $x . '.53';
    $desc = $entry['15'];
    $value = $entry['53'];

    //If alarms are enabled
    if ($entry['22'] == '2' && ! ($desc == 'Battery' && $value == '0') && $desc != 'Power') {
        $low_limit = $entry['11'];
        $high_limit = $entry['12'];
    } else {
        $low_limit = null;
        $high_limit = null;
    }

    discover_sensor($valid['sensor'], 'count', $device, $oid, strval($x),
        $device['os'], $desc, $divisor, 1, $low_limit, null, null, $high_limit, $value / $divisor);
}
