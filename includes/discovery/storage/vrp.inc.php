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
    $vrp_tmp = snmp_get_multi_oid($device, ['hwStorageDescr.1', 'hwStorageSpaceFree.1', 'hwStorageSpace.1'], '-OUQs', 'HUAWEI-FLASH-MAN-MIB');
/*
 * HUAWEI-FLASH-MAN-MIB::hwStorageType.1 = INTEGER: flash(1)
 * HUAWEI-FLASH-MAN-MIB::hwStorageSpace.1 = INTEGER: 206324 kbytes
 * HUAWEI-FLASH-MAN-MIB::hwStorageSpaceFree.1 = INTEGER: 59084 kbytes
 * HUAWEI-FLASH-MAN-MIB::hwStorageName.1 = STRING: flash:
 * HUAWEI-FLASH-MAN-MIB::hwStorageDescr.1 = STRING: System Flash
 */
    $fstype = 'dsk';
    $descr = $vrp_tmp['hwStorageDescr.1'];
    $units = 1024;
    $index = 1;
    if (is_numeric($vrp_tmp['hwStorageSpace.1']) && is_numeric($vrp_tmp['hwStorageSpaceFree.1'])) {
        $total = $vrp_tmp['hwStorageSpace.1'];
        $used = $total - $vrp_tmp['hwStorageSpaceFree.1'];
        discover_storage($valid_storage, $device, $index, $fstype, 'vrp', $descr, $total, $units, $used);
    }
    unset($vrp_tmp);
}
