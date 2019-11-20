<?php
/**
 * gaia.inc.php
 *
 * LibreNMS storage discovery module for Check Point GAIA
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
 * @author     Neil Lathwood <gh+n@laf.io>
 */

if ($device['os'] === 'gaia') {
    $gaia_tmp = snmpwalk_cache_double_oid($device, 'multiDiskTable', array(), 'CHECKPOINT-MIB');

    $fstype = "dsk";

    foreach ($gaia_tmp as $index => $data) {
        $descr = $data['multiDiskName'];
        $units = 1024;
        if (is_numeric($data['multiDiskSize']) && is_numeric($data['multiDiskUsed'])) {
            $total = $data['multiDiskSize'];
            $used = $data['multiDiskUsed'];
            discover_storage($valid_storage, $device, $index, $fstype, 'gaia', $descr, $total, $units, $used);
        }
    }
    unset($gaia_tmp);
}
