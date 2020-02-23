<?php
/**
 * ict-mps.inc.php
 *
 * LibreNMS voltage sensor discovery module for ICT Modular Power System
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
$inputVoltage = trim(snmp_get($device, 'inputVoltage.0', '-Oqv', 'ICT-MODULAR-POWER-SYSTEM-MIB'), '" ');
if (!empty($inputVoltage)) {
    $divisor = 1;
    $index = 0;
    $oid = '.1.3.6.1.4.1.39145.13.6.0';
    $descr = 'Input Voltage (VAC)';
    $type = 'ict-mps';
    $currentValue = $inputVoltage / $divisor;
    
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}

// Output Voltage
$outputVoltage = trim(snmp_get($device, 'outputVoltage.0', '-Oqv', 'ICT-MODULAR-POWER-SYSTEM-MIB'), '" ');
if (!empty($outputVoltage)) {
    $divisor = 1;
    $index = 1;
    $oid = '.1.3.6.1.4.1.39145.13.7.0';
    $descr = 'Output Voltage (VDC)';
    $type = 'ict-mps';
    $currentValue = $outputVoltage / $divisor;

    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}

// Module Voltage
$voltages = snmpwalk_cache_oid($device, 'moduleTable', [], 'ICT-MODULAR-POWER-SYSTEM-MIB');
if (!empty($voltages)) {
    foreach ($voltages as $index => $entry) {
        $module_status_oid = '.1.3.6.1.4.1.39145.13.10.1.4.' . $index;
        $divisor = 1;
        $descr = "Module #$index Voltage (VDC)";
        $type = 'ict-mps';
        $current_value = $entry["moduleVoltage"];

        discover_sensor($valid['sensor'], 'voltage', $device, $module_status_oid, $index+2, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
    }
}
