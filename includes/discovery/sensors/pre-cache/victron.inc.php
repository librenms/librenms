<?php

/**
 * victron.inc.php
 *
 * LibreNMS pre-cache discovery module for Victron Energy GX devices
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

// AC Input scalar OIDs
echo 'acInput ';
$pre_cache['acInL1Frequency'] = [0 => ['acInL1Frequency' => snmp_get($device, '.1.3.6.1.4.1.41312.1.1.2.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['acInL1Current'] = [0 => ['acInL1Current' => snmp_get($device, '.1.3.6.1.4.1.41312.1.1.3.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['acInL1Voltage'] = [0 => ['acInL1Voltage' => snmp_get($device, '.1.3.6.1.4.1.41312.1.1.4.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['acInL1Power'] = [0 => ['acInL1Power' => snmp_get($device, '.1.3.6.1.4.1.41312.1.1.5.0', '-Ovq', 'VICTRON-MIB')]];

// AC Output scalar OIDs
echo 'acOutput ';
$pre_cache['acOutL1Frequency'] = [0 => ['acOutL1Frequency' => snmp_get($device, '.1.3.6.1.4.1.41312.1.2.1.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['acOutL1Current'] = [0 => ['acOutL1Current' => snmp_get($device, '.1.3.6.1.4.1.41312.1.2.2.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['acOutL1Voltage'] = [0 => ['acOutL1Voltage' => snmp_get($device, '.1.3.6.1.4.1.41312.1.2.3.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['acOutL1Power'] = [0 => ['acOutL1Power' => snmp_get($device, '.1.3.6.1.4.1.41312.1.2.4.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['acOutL1NominalPower'] = [0 => ['acOutL1NominalPower' => snmp_get($device, '.1.3.6.1.4.1.41312.1.2.5.0', '-Ovq', 'VICTRON-MIB')]];

// Alarm scalar OIDs
echo 'alarms ';
$pre_cache['alarmGridLost'] = [0 => ['alarmGridLost' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.2.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['alarmHighTemperature'] = [0 => ['alarmHighTemperature' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.3.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['alarmHighVoltage'] = [0 => ['alarmHighVoltage' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.4.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['alarmHighVoltageAcOut'] = [0 => ['alarmHighVoltageAcOut' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.5.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['alarmLowSoc'] = [0 => ['alarmLowSoc' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.6.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['alarmLowVoltage'] = [0 => ['alarmLowVoltage' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.7.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['alarmLowVoltageAcOut'] = [0 => ['alarmLowVoltageAcOut' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.8.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['alarmOverload'] = [0 => ['alarmOverload' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.9.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['alarmShortCircuit'] = [0 => ['alarmShortCircuit' => snmp_get($device, '.1.3.6.1.4.1.41312.1.3.12.0', '-Ovq', 'VICTRON-MIB')]];

// DC/Battery scalar OIDs
echo 'dcBattery ';
$pre_cache['dcCurrent'] = [0 => ['dcCurrent' => snmp_get($device, '.1.3.6.1.4.1.41312.1.4.1.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['dcPower'] = [0 => ['dcPower' => snmp_get($device, '.1.3.6.1.4.1.41312.1.4.2.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['dcRippleVoltage'] = [0 => ['dcRippleVoltage' => snmp_get($device, '.1.3.6.1.4.1.41312.1.4.3.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['dcTemperature'] = [0 => ['dcTemperature' => snmp_get($device, '.1.3.6.1.4.1.41312.1.4.4.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['dcVoltage'] = [0 => ['dcVoltage' => snmp_get($device, '.1.3.6.1.4.1.41312.1.4.5.0', '-Ovq', 'VICTRON-MIB')]];

// System scalar OIDs
echo 'system ';
$pre_cache['systemSoc'] = [0 => ['systemSoc' => snmp_get($device, '.1.3.6.1.4.1.41312.1.7.1.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['systemState'] = [0 => ['systemState' => snmp_get($device, '.1.3.6.1.4.1.41312.1.7.2.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['systemTimeToGo'] = [0 => ['systemTimeToGo' => snmp_get($device, '.1.3.6.1.4.1.41312.1.7.3.0', '-Ovq', 'VICTRON-MIB')]];
$pre_cache['systemYieldPower'] = [0 => ['systemYieldPower' => snmp_get($device, '.1.3.6.1.4.1.41312.1.7.4.0', '-Ovq', 'VICTRON-MIB')]];

// PV Trackers - these are table entries, walk them
echo 'pvTrackers ';
$pre_cache['pvVoltage'] = snmpwalk_cache_oid($device, 'pvVoltage', [], 'VICTRON-MIB');
$pre_cache['pvPower'] = snmpwalk_cache_oid($device, 'pvPower', [], 'VICTRON-MIB');
