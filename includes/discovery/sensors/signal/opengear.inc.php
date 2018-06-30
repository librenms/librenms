<?php
/**
 * opengear.inc.php
 *
 * LibreNMS signal sensor discovery module for Opengear Devices
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
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

// 3G Signal

$threeG_signal = trim(snmp_get($device, 'ogCellModem3gRssi.1', '-Oqv', 'OG-STATUSv2-MIB'), '" ');
if (!empty($threeG_signal)) {
    $divisor = 1;
    $index = '11.1';
    $descr = 'Cellular 3G RSSI';
    $type = 'opengear';
    $oid = '.1.3.6.1.4.1.25049.17.17.1.11.1';
    $current_value = $threeG_signal / $divisor;
    discover_sensor($valid['sensor'], 'signal', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current_value);
}

// 4G Signal

$fourG_signal = trim(snmp_get($device, 'ogCellModem4gRssi.1', '-Oqv', 'OG-STATUSv2-MIB'), '" ');
if (!empty($fourG_signal)) {
    $divisor = 1;
    $index = '12.1';
    $descr = 'Cellular 4G RSSI';
    $type = 'opengear';
    $oid = '.1.3.6.1.4.1.25049.17.17.1.12.1';
    $current_value = $fourG_signal / $divisor;
    discover_sensor($valid['sensor'], 'signal', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current_value);
}
