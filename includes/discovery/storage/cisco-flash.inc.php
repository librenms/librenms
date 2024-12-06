<?php
/**
 * cisco-flash.inc.php
 *
 * LibreNMS storage discovery module for Cisco Flash
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2022 Félix Bouynot
 * @author     Félix Bouynot <felix.bouynot@setenforce.one>
 */

use LibreNMS\Util\Number;

if ($device['os_group'] == 'cisco') {
    $ciscoFlashPartitionName = snmpwalk_cache_oid($device, 'ciscoFlashPartitionName', null, 'CISCO-FLASH-MIB');
    $ciscoFlashDeviceName = snmpwalk_cache_oid($device, 'ciscoFlashDeviceName', null, 'CISCO-FLASH-MIB');
    foreach ($ciscoFlashPartitionName as $index => $partitionName) {
        $name = is_array($ciscoFlashDeviceName)? array_shift($ciscoFlashDeviceName[$index[0]]) . '(' . array_shift($partitionName) . '):': array_shift($partitionName);
        $oids = array('ciscoFlashPartitionSize.' . $index, 'ciscoFlashPartitionFreeSpace.' . $index, 'ciscoFlashPartitionSizeExtended.' . $index, 'ciscoFlashPartitionFreeSpaceExtended.' . $index);
        $entry = snmp_get_multi($device, $oids, '-OQUs', 'CISCO-FLASH-MIB');
        $entry = array_shift($entry);
        $storage_size = (Number::cast($entry['ciscoFlashPartitionSize']) === 4294967295 ? $entry['ciscoFlashPartitionSizeExtended'] : $entry['ciscoFlashPartitionSize']);
        $storage_free = (Number::cast($entry['ciscoFlashPartitionFreeSpace']) === 4294967295 ? $entry['ciscoFlashPartitionFreeSpaceExtended'] : $entry['ciscoFlashPartitionFreeSpace']);
        $storage_used = $storage_size - $storage_free;
        $storage_units = 1;
        discover_storage($valid_storage, $device, $index, 'flash', 'cisco-flash', $name, $storage_size, $storage_units, $storage_used);
    }
    unset ($ciscoFlashPartitionName, $storage_size, $storage_free, $storage_used, $storage_units, $oids, $entry);
}
