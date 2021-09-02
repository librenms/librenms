<?php
/**
 * apc.inc.php
 *
 * LibreNMS os sensor pre-cache module for APC
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
echo 'coolingUnitStatusAnalogEntry ';
$pre_cache['cooling_unit_analog'] = snmpwalk_cache_oid($device, 'coolingUnitStatusAnalogEntry', [], 'PowerNet-MIB');

echo 'upsPhaseNumInputPhases ';
$pre_cache['apcups_phase_count'] = snmp_get($device, 'upsPhaseNumInputPhases.1', '-OQv', 'PowerNet-MIB');

echo 'memSensorsStatusTable ';
$pre_cache['mem_sensors_status'] = snmpwalk_cache_oid($device, 'memSensorsStatusTable', [], 'PowerNet-MIB', null, '-OQUse');

echo 'memSensorsStatusSysTempUnits ';
$pre_cache['memSensorsStatusSysTempUnits'] = snmp_get($device, 'memSensorsStatusSysTempUnits.0', '-OQv', 'PowerNet-MIB');
