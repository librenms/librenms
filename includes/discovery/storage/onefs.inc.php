<?php
/**
 * onefs.inc.php
 *
 * LibreNMS storage module for OneFS
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
if ($device['os'] === 'onefs') {
    $oids = snmp_get_multi_oid($device, ['ifsTotalBytes.0', 'ifsUsedBytes.0', 'ifsAvailableBytes.0'], '-OUQn', 'ISILON-MIB');

    $fstype = 'ifs';
    $descr = 'Internal File System';
    $units = 1024;
    $index = 0;
    $free = $oids['.1.3.6.1.4.1.12124.1.3.3.0'];
    $total = $oids['.1.3.6.1.4.1.12124.1.3.1.0'];
    $used = $oids['.1.3.6.1.4.1.12124.1.3.2.0'];
    if (is_numeric($free) && is_numeric($total)) {
        discover_storage($valid_storage, $device, $index, $fstype, 'onefs', $descr, $total, $units, $used);
    }
    unset($oids);
}
