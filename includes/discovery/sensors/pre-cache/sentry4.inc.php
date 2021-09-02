<?php
/**
 * sentry4.inc.php
 *
 * LibreNMS pre-cache discovery module for Sentry4
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
echo 'st4HumidSensorConfigTable ';
$pre_cache['sentry4_humid'] = snmpwalk_cache_oid($device, 'st4HumidSensorConfigTable', [], 'Sentry4-MIB');

echo 'st4HumidSensorMonitorTable ';
$pre_cache['sentry4_humid'] = snmpwalk_cache_oid($device, 'st4HumidSensorMonitorTable', $pre_cache['sentry4_humid'], 'Sentry4-MIB');

echo 'st4HumidSensorEventConfigTable ';
$pre_cache['sentry4_humid'] = snmpwalk_cache_oid($device, 'st4HumidSensorEventConfigTable', $pre_cache['sentry4_humid'], 'Sentry4-MIB');

echo 'st4TempSensorConfigTable ';
$pre_cache['sentry4_temp'] = snmpwalk_cache_oid($device, 'st4TempSensorConfigTable', [], 'Sentry4-MIB');

echo 'st4TempSensorMonitorTable ';
$pre_cache['sentry4_temp'] = snmpwalk_cache_oid($device, 'st4TempSensorMonitorTable', $pre_cache['sentry4_temp'], 'Sentry4-MIB');

echo 'st4TempSensorEventConfigTable ';
$pre_cache['sentry4_temp'] = snmpwalk_cache_oid($device, 'st4TempSensorEventConfigTable', $pre_cache['sentry4_temp'], 'Sentry4-MIB');

echo 'st4InputCordConfigTable ';
$pre_cache['sentry4_input'] = snmpwalk_cache_oid($device, 'st4InputCordConfigTable', [], 'Sentry4-MIB');

echo 'st4InputCordMonitorTable ';
$pre_cache['sentry4_input'] = snmpwalk_cache_oid($device, 'st4InputCordMonitorTable', $pre_cache['sentry4_input'], 'Sentry4-MIB');

echo 'st4InputCordEventConfigTable ';
$pre_cache['sentry4_input'] = snmpwalk_cache_oid($device, 'st4InputCordEventConfigTable', $pre_cache['sentry4_input'], 'Sentry4-MIB');
