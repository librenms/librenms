<?php
/**
 * netbotz.inc.php
 *
 * LibreNMS pre-cache discovery module for Netbotz
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

$pre_cache['netbotz_airflow']     = snmpwalk_cache_oid($device, 'airFlowSensorTable', array(), 'NETBOTZV2-MIB');
$pre_cache['netbotz_temperature'] = snmpwalk_cache_oid($device, 'dewPointSensorTable', array(), 'NETBOTZV2-MIB');
$pre_cache['netbotz_state']       = snmpwalk_cache_oid($device, 'dryContactSensorTable', array(), 'NETBOTZV2-MIB', null, '-OeQUs');
$pre_cache['netbotz_state']       = snmpwalk_cache_oid($device, 'doorSwitchSensorTable', $pre_cache['netbotz_state'], 'NETBOTZV2-MIB', null, '-OeQUs');
$pre_cache['netbotz_state']       = snmpwalk_cache_oid($device, 'cameraMotionSensorTable', $pre_cache['netbotz_state'], 'NETBOTZV2-MIB', null, '-OeQUs');
