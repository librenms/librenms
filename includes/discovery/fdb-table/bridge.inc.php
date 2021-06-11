<?php
/**
 * bridge.inc.php
 *
 * Discover FDB data with Q-BRIDGE-MIB and BRIDGE-MIB
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
 * @copyright  LibreNMS contributors
 * @author     Tony Murray <murraytony@gmail.com>
 * @author     cjwbath
 */

// Try Q-BRIDGE-MIB::dot1qTpFdbPort first
$fdbPort_table = snmpwalk_group($device, 'dot1qTpFdbPort', 'Q-BRIDGE-MIB');
if (! empty($fdbPort_table)) {
    echo 'Q-BRIDGE-MIB:';
    $data_oid = 'dot1qTpFdbPort';
} else {
    // If we don't have Q-BRIDGE-MIB::dot1qTpFdbPort, try BRIDGE-MIB::dot1dTpFdbPort
    $dot1d = snmpwalk_group($device, 'dot1dTpFdbPort', 'BRIDGE-MIB', 0);
    $data_oid = 'dot1dTpFdbPort';
    if (! empty($dot1d)) {
        echo 'BRIDGE-MIB: ';
        $fdbPort_table = [0 => $dot1d];  // dont' have VLAN, so use 0
    }
}

if (! empty($fdbPort_table)) {
    // Build dot1dBasePort to port_id dictionary
    $portid_dict = [];
    $dot1dBasePortIfIndex = snmpwalk_group($device, 'dot1dBasePortIfIndex', 'BRIDGE-MIB');
    foreach ($dot1dBasePortIfIndex as $portLocal => $data) {
        if (isset($data['dot1dBasePortIfIndex'])) {
            $port = get_port_by_index_cache($device['device_id'], $data['dot1dBasePortIfIndex']);
            $portid_dict[$portLocal] = $port['port_id'];
        }
    }

    // Build VLAN fdb index to real VLAN ID dictionary
    $vlan_cur_table = snmpwalk_group($device, 'dot1qVlanFdbId', 'Q-BRIDGE-MIB', 2);
    $vlan_fdb_dict = [];

    // Indexed first by dot1qVlanTimeMark, which we ignore
    foreach ($vlan_cur_table as $dot1qVlanTimeMark => $a) {
        // Then by VLAN ID mapped to a single member array with the dot1qVlanFdbId
        foreach ($a as $vid => $data) {
            // Flip it round into the dictionary
            $vlan_fdb_dict[$data['dot1qVlanFdbId']] = $vid;
        }
    }

    // Collect data and populate $insert
    foreach ($fdbPort_table as $vlanIndex => $data) {
        // Look the dot1qVlanFdbId up to a real VLAN number; if undefined assume the
        // index *is* the VLAN number. Code in fdb-table.inc.php to map to the
        // device VLANs table should catch anything invalid.
        $vlan = isset($vlan_fdb_dict[$vlanIndex]) ? $vlan_fdb_dict[$vlanIndex] : $vlanIndex;

        foreach ($data[$data_oid] ?? [] as $mac => $dot1dBasePort) {
            if ($dot1dBasePort == 0) {
                d_echo("No port known for $mac\n");
                continue;
            }
            $mac_address = implode(array_map('zeropad', explode(':', $mac)));
            if (strlen($mac_address) != 12) {
                d_echo("MAC address padding failed for $mac\n");
                continue;
            }
            $port_id = $portid_dict[$dot1dBasePort];
            $vlan_id = isset($vlans_dict[$vlan]) ? $vlans_dict[$vlan] : 0;
            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
            d_echo("vlan $vlan mac $mac_address port ($dot1dBasePort) $port_id\n");
        }
    }
}

echo PHP_EOL;
