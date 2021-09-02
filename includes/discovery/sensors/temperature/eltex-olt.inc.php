<?php
/**
 * eltex-olt.inc.php
 *
 * LibreNMS temperature discovery module for Eltex OLT
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$tmp_eltex = snmp_get_multi_oid($device, 'ltp8xSensor1Temperature.0 ltp8xSensor2Temperature.0 ltp8xSensor1TemperatureExt.0 ltp8xSensor2TemperatureExt.0', '-OUQn', 'ELTEX-LTP8X-STANDALONE');

if (is_numeric($tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.10.0'])) {
    $oid = '.1.3.6.1.4.1.35265.1.22.1.10.10.0';
    $index = 'ltp8xSensor1Temperature';
    $type = 'eltex-olt';
    $descr = 'Sensor 1 Temp';
    $divisor = 1;
    $current = $tmp_eltex[$oid];
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

if (is_numeric($tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.11.0'])) {
    $oid = '.1.3.6.1.4.1.35265.1.22.1.10.11.0';
    $index = 'ltp8xSensor2Temperature';
    $type = 'eltex-olt';
    $descr = 'Sensor 2 Temp';
    $divisor = 1;
    $current = $tmp_eltex[$oid];
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

if (is_numeric($tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.12.0']) && $tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.12.0'] != 65535) {
    $oid = '.1.3.6.1.4.1.35265.1.22.1.10.12.0';
    $index = 'ltp8xSensor1TemperatureExt';
    $type = 'eltex-olt';
    $descr = 'Sensor 1 External Temp';
    $divisor = 1;
    $current = $tmp_eltex[$oid];
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

if (is_numeric($tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.13.0']) && $tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.13.0'] != 65535) {
    $oid = '.1.3.6.1.4.1.35265.1.22.1.10.13.0';
    $index = 'ltp8xSensor2TemperatureExt';
    $type = 'eltex-olt';
    $descr = 'Sensor 2 External Temp';
    $divisor = 1;
    $current = $tmp_eltex[$oid];
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

unset($tmp_eltex);
