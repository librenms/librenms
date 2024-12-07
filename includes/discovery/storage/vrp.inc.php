<?php
/**
 * oceanstor.inc.php
 *
 * LibreNMS storage discovery module for Huawei OceanStor
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
 * @copyright  2020 PipoCanaja
 * @author     Neil Lathwood <gh+n@laf.io>
 */

if ($device['os'] === 'vrp') {
    $vrp_tmp = snmpwalk_cache_oid($device, 'hwStorageEntry', null, 'HUAWEI-FLASH-MAN-MIB');
/*
 * array (
 *   1 =>
 *   array (
 *     'hwStorageType' => 'flash',
 *     'hwStorageSpace' => '206324',
 *     'hwStorageSpaceFree' => '59084',
 *     'hwStorageName' => 'flash:',
 *     'hwStorageDescr' => 'System Flash',
 *   ),
 * )
 */
    if (is_array($vrp_tmp)) {
        echo 'storageEntry ';
        foreach ($vrp_tmp as $index => $storage) {
            $fstype = 'dsk';
            $descr = $storage['hwStorageDescr'];
            if (empty($descr)) {
                $descr = $storage['hwStorageName'];
            }
            $units = 1024;
            if (is_numeric($storage['hwStorageSpace']) && is_numeric($storage['hwStorageSpaceFree'])) {
                $total = $storage['hwStorageSpace'] * $units;
                $used = $total - $storage['hwStorageSpaceFree'] * $units;
                discover_storage($valid_storage, $device, $index, $fstype, 'vrp', $descr, $total, $units, $used);
            }
        }
    }
    unset($vrp_tmp);
}
