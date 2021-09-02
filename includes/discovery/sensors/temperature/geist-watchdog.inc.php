<?php
/**
 * geist-watchdog.inc.php
 *
 * LibreNMS temperature discovery module for Geist Watchdog
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
$value = snmp_get($device, 'climateTempC', '-Oqv', 'GEIST-MIB-V3');
if ($value) {
    $current_oid = '.1.3.6.1.4.1.21239.2.2.1.5.1';
    $descr = 'Temperature';
    discover_sensor($valid['sensor'], 'temperature', $device, $current_oid, 'climateTempC', 'geist-watchdog', $descr, 1, 1, null, null, null, null, $value);
}

$value = snmp_get($device, 'climateTempF', '-Oqv', 'GEIST-MIB-V3');
if ($value) {
    $current_oid = '.1.3.6.1.4.1.21239.2.2.1.6.1';
    $descr = 'Temperature';
    discover_sensor($valid['sensor'], 'temperature', $device, $current_oid, 'climateTempF', 'geist-watchdog', $descr, 1, 1, null, null, null, null, $value, null, null, null, 'fahrenheit_to_celsius');
}

$temp_table = snmpwalk_cache_oid($device, 'tempSensorTable', [], 'GEIST-MIB-V3');

foreach ($temp_table as $index => $temp_data) {
    if ($temp_data['tempSensorAvail'] == 1) {
        $current_oid = '.1.3.6.1.4.1.21239.2.4.1.5.' . $index;
        $descr = $temp_data['tempSensorName'] . ': #' . $temp_data['tempSensorSerial'];
        $value = $temp_data['tempSensorTempC'];
        discover_sensor($valid['sensor'], 'temperature', $device, $current_oid, $index, 'geist-watchdog', $descr, 1, 1, null, null, null, null, $value);
    }
}

unset($temp_table);
