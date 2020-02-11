<?php
/**
 * procurve.inc.php
 *
 * Discover ProCurve FDB data with Q-BRIDGE-MIB and BRIDGE-MIB
 * (based on bridge.inc.php). ProCurve indexes the dot1qTpFdbTable using
 * VLAN index numbers from dot1qVlanCurrentTable, rather than actual VLAN
 * IDs, so there needs to be further mapping to properly get the VLAN relating 
 * to eachFDB entry.
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
 * @copyright  LibreNMS Contributors
 * @author     Tony Murray <murraytony@gmail.com>
 * @author     cjwbath
 */

// Try Q-BRIDGE-MIB::dot1qTpFdbPort first
$fdbPort_table = snmpwalk_group($device, 'dot1qTpFdbPort', 'Q-BRIDGE-MIB');
if (!empty($fdbPort_table)) {
    echo 'Q-BRIDGE-MIB:';
    $data_oid = 'dot1qTpFdbPort';
} else {
    // If we don't have Q-BRIDGE-MIB::dot1qTpFdbPort, try BRIDGE-MIB::dot1dTpFdbPort
    $dot1d = snmpwalk_group($device, 'dot1dTpFdbPort', 'BRIDGE-MIB', 0);
    $data_oid = 'dot1dTpFdbPort';
    if (!empty($dot1d)) {
        echo 'BRIDGE-MIB: ';
        $fdbPort_table = array(0 => $dot1d);  // dont' have VLAN, so use 0
    }
}

if (!empty($fdbPort_table)) {
    // Build dot1dBasePort to port_id dictionary
    $portid_dict = array();
    $dot1dBasePortIfIndex = snmpwalk_group($device, 'dot1dBasePortIfIndex', 'BRIDGE-MIB');
    foreach ($dot1dBasePortIfIndex as $portLocal => $data) {
        $port = get_port_by_index_cache($device['device_id'], $data['dot1dBasePortIfIndex']);
        $portid_dict[$portLocal] = $port['port_id'];
    }

    // Build VLAN fdb index to real VLAN ID dictionary
    $vlan_cur_table = snmpwalk_group($device, 'dot1qVlanFdbId', 'Q-BRIDGE-MIB', 2);
    $vlan_fdb_dict = array();
    // Only interested in the zero dot1qVlanTimeMark
    foreach ($vlan_cur_table[0] as $vid => $a) {
        // Flip the array and flatten the extra single-key dimension
        $vlan_fdb_dict[$a['dot1qVlanFdbId']] = $vid;
    }

    // Collect data and populate $insert
    foreach ($fdbPort_table as $vlanIndex => $data) {
        // Look the dot1qVlanFdbId up to a real VLAN number
        $vlan = isset($vlan_fdb_dict[$vlanIndex]) ? $vlan_fdb_dict[$vlanIndex] : 0;

        foreach ($data[$data_oid] as $mac => $dot1dBasePort) {
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
            // Lookup LibreNMS internal ID for that VLAN
            $vlan_id = isset($vlans_dict[$vlan]) ? $vlans_dict[$vlan] : 0;
            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
            d_echo("vlan $vlan mac $mac_address port ($dot1dBasePort) $port_id\n");
        }
    }
}

echo PHP_EOL;
