<?php
/**
 * vrp.inc.php
 *
 * Discover FDB data with HUAWEI-L2MAM-MIB
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
 */

$fdbPort_table = snmpwalk_group($device, 'hwDynFdbPort', 'HUAWEI-L2MAM-MIB');

if (!empty($fdbPort_table)) {
    echo 'HUAWEI-L2MAM-MIB:';
    $data_oid = 'hwDynFdbPort';
    // Collect data and populate $insert
    foreach ($fdbPort_table as $mac => $data) {
        foreach ($data[$data_oid] as $vlan => $basePort) {
            $ifIndex=$basePort[0];
            $port = get_port_by_index_cache($device['device_id'], $ifIndex);
            $port_id = $port['port_id'];
            $mac_address = implode(array_map('zeropad', explode(':', $mac)));
            if (strlen($mac_address) != 12) {
                d_echo("MAC address padding failed for $mac\n");
                continue;
            }
            $vlan_id = isset($vlans_dict[$vlan]) ? $vlans_dict[$vlan] : 0;
            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
            d_echo("vlan $vlan mac $mac_address port ($ifIndex) $port_id\n");
        }
    }
}

echo PHP_EOL;
