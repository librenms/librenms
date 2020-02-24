<?php
/**
 * ict-swi.inc.php
 *
 * LibreNMS voltage sensor discovery module for ICT Sine Wave Inverter
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

// Battery Voltage
$batteryVoltage = (int)(snmp_get($device, 'batteryVoltage.0', '-Oqv', 'ICT-SINE-WAVE-INVERTER-MIB'));
if ($batteryVoltage >= 0) {
    $divisor = 1;
    $index = 0;
    $oid = '.1.3.6.1.4.1.39145.12.6.0';
    $descr = 'Battery Voltage (VDC)';
    $type = 'ict-swi';
    $currentValue = $batteryVoltage / $divisor;
    
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}

// Inverter Voltage
$inverterVoltage = (int)(snmp_get($device, 'inverterVoltage.0', '-Oqv', 'ICT-SINE-WAVE-INVERTER-MIB'));
if ($inverterVoltage >= 0) {
    $divisor = 1;
    $index = 1;
    $oid = '.1.3.6.1.4.1.39145.12.7.0';
    $descr = 'Inverter Voltage (VAC)';
    $type = 'ict-swi';
    $currentValue = $inverterVoltage / $divisor;

    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}

