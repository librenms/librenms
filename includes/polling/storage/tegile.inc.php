<?php
/**
 * tegile.inc.php
 *
 * LibreNMS storage polling module for Tegile Storage
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */
if (!is_array($storage_cache['tegile'])) {
    $storage_cache['tegile'] = snmpwalk_cache_oid($device, 'poolEntry', null, 'TEGILE-MIB');
    d_echo($storage_cache);
}
$entry = $storage_cache['tegile'][$storage[storage_index]];
$storage['units'] = 1;
//Tegile uses a high 32bit counter and a low 32bit counter to make a 64bit counter. Storage units are in bytes.
$storage['size'] = (($entry['poolSizeHigh'] << 32 ) + $entry['poolSizeLow']) * $storage['units'];
$storage['used'] = (($entry['poolUsedSizeHigh'] << 32 ) + $entry['poolUsedSizeLow']) * $storage['units'];
$storage['free'] = ($storage['size'] - $storage['used']);

if (!is_array($storage_cache['tegile2'])) {
    $storage_cache['tegile'] = snmpwalk_cache_oid($device, 'projectEntry', null, 'TEGILE-MIB');
    d_echo($storage_cache);
}
$entry = $storage_cache['tegile2'][$storage[storage_index]];
$storage['units'] = 1;
//Tegile uses a high 32bit counter and a low 32bit counter to make a 64bit counter. Storage units are in bytes.
$storage['size'] = (($entry['projectDadaSizeHigh'] << 32 ) + $entry['projectDataSizeLow']) * $storage['units'];
$storage['
$storage['used'] = (($entry['poolUsedSizeHigh'] << 32 ) + $entry['poolUsedSizeLow']) * $storage['units'];
$storage['free'] = ($storage['size'] - $storage['used']);




$tegile_storage = snmpwalk_cache_oid($device, 'projectEntry', null, 'TEGILE-MIB');
    foreach ($tegile_storage as $index => $storage) {
        $units  = 1;
        $descr  = $storage['projectName'];
        //Tegile uses a high 32bit counter and a low 32bit counter to make a 64bit counter. Size units are in bytes
        $size = (($storage['projectDataSizeHigh'] << 32 ) + $storage['projectDataSizeLow']) * $units;
        $free = (($storage['projectFreeSizeHigh'] << 32 ) + $storage['projectFreeSizeLow']) * $units;
        $used =  ($size - $free);
        if (is_numeric($index)) {
            discover_storage($valid_storage, $device, $index, $fstype, 'tegile', $descr, $size, $units, $used);
        }
        unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
    }
}

