<?php
/**
 * ict-psu.inc.php
 *
 * LibreNMS voltage sensor discovery module for ICT Digital Series Power Supply
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

// Input Voltage
// SNMPv2-SMI::enterprises.39145.11.6.0 = STRING: "120" -- inputVoltage

$inputVoltage_oid = '.1.3.6.1.4.1.39145.11.6.0';
$inputVoltage = trim(snmp_get($device, $inputVoltage_oid, '-Oqv'), '" ');
if (!empty($inputVoltage)) {
    $divisor = 1;
    $index = '11.6.0';
    $descr = 'Input Voltage';
    $type = 'ict-psu';
    $voltage = $inputVoltage / $divisor;

    discover_sensor($valid['sensor'], 'voltage', $device, $inputVoltage_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $votlage);
}

// Output Voltage
// SNMPv2-SMI::enterprises.39145.11.7.0 = STRING: "55.2" -- outputVoltage

$outputVoltage_oid = '.1.3.6.1.4.1.39145.11.7.0';
$outputVoltage = trim(snmp_get($device, $outputVoltage_oid, '-Oqv'), '" ');
if (!empty($outputVoltage)) {

    $divisor = 1;
    $index = '11.7.0';
    $descr = 'Output Voltage';
    $type = 'ict-psu';
    $voltage = $outputVoltage / $divisor;

    discover_sensor($valid['sensor'], 'voltage', $device, $outputVoltage_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $votlage);
}
