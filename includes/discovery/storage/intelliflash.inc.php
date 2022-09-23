<?php
/**
 * tegile.inc.php
 *
 * LibreNMS storage discovery module for Tegile Storage
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
 * along with this program.  If not, see <http://www.storage snmpgnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */
use LibreNMS\Config;

if ($device['os'] == 'intelliflash') {
    $tegile_storage = snmpwalk_cache_oid($device, 'poolEntry', null, 'TEGILE-MIB');
    if (is_array($tegile_storage)) {
        echo 'poolEntry ';
        foreach ($tegile_storage as $index => $storage) {
            $units = 1;
            $fstype = $storage['poolState'];
            $descr = $storage['poolName'];
            //Tegile uses a high 32bit counter and a low 32bit counter to make a 64bit counter. Storage units are in bytes.
            $size = (($storage['poolSizeHigh'] << 32) + $storage['poolSizeLow']) * $units;
            $used = (($storage['poolUsedSizeHigh'] << 32) + $storage['poolUsedSizeLow']) * $units;
            if (is_numeric($index)) {
                discover_storage($valid_storage, $device, $index, $fstype, 'intelliflash-pl', $descr, $size, $units, $used);
            }
            unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
        }
    }
    $tegile_storage2 = snmpwalk_cache_oid($device, 'projectEntry', null, 'TEGILE-MIB');
    if (is_array($tegile_storage2)) {
        echo 'projectEntry ';
        foreach ($tegile_storage2 as $index => $storage) {
            $units = 1;
            $descr = $storage['projectName'];
            $fstype = 1;
            $pdsh = ($storage['projectDataSizeHigh'] << 32);
            $pdsl = ($storage['projectDataSizeLow']);
            $pdst = (($pdsh + $pdsl) * $units);
            $pfsh = ($storae['projectFreeSizeHigh'] << 32);
            $pfsl = ($storage['projectFreeSizeLow']);
            $pfst = (($pfsh + $pfsl) * $units);
            //Tegile uses a high 32bit counter and a low 32bit counter to make a 64bit counter. Storage units are in bytes.
            $size = ($pdst + $pfst);
            $used = ($pdst);
            $free = ($pfst);
            if (is_numeric($index)) {
                discover_storage($valid_storage, $device, $index, $fstype, 'intelliflash-pr', $descr, $size, $units, $used);
            }
            unset($deny, $fstype, $descr, $size, $used2, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
        }
    }
}
