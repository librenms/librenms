<?php
/**
 * raritan-pdu.inc.php
 *
 * LibreNMS pre-cache discovery module for Raritan
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
echo 'inletTable ';
$pre_cache['raritan_inletTable'] = snmpwalk_group($device, 'inletTable', 'PDU-MIB');

echo 'inletPoleTable ';
$pre_cache['raritan_inletPoleTable'] = snmpwalk_group($device, 'inletPoleTable', 'PDU-MIB', 2);

echo 'inletLabel ';
$pre_cache['raritan_inletLabel'] = snmpwalk_cache_oid($device, 'inletLabel', [], 'PDU2-MIB');

echo 'externalSensors';
$pre_cache['raritan_extSensorConfig'] = snmpwalk_cache_oid($device, 'externalSensorConfigurationTable', [], 'PDU2-MIB');

echo 'externalSensorMeasurementsTable';
$pre_cache['raritan_extSensorMeasure'] = snmpwalk_cache_oid($device, 'externalSensorMeasurementsTable', [], 'PDU2-MIB');
