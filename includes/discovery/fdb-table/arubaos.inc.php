<?php
/**
 * arubaos.inc.php
 *
 * Discover ArubaOS FDB data with Q-BRIDGE-MIB and BRIDGE-MIB
 *  based on bridge.inc.php
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
 * @copyright  2020 LibreNMS
 * @author     Ken Lui <tmpest1@yahoo.com>
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

    foreach ($fdbPort_table as $vlan => $data) {
        d_echo("VLAN: $vlan\n");
        $dot1dBasePortIfIndex = snmpwalk_group($device, 'dot1dBasePortIfIndex', 'BRIDGE-MIB', 1, $dot1dBasePortIfIndex, null, $vlan);
    }

    foreach ($dot1dBasePortIfIndex as $portLocal => $data) {
        $port = get_port_by_index_cache($device['device_id'], $data['dot1dBasePortIfIndex']);
        $portid_dict[$portLocal] = $port['port_id'];
    }

    // Collect data and populate $insert
    foreach ($fdbPort_table as $vlan => $data) {
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
            if ($port_id === null) {
                $port_id = 0;
            }
            $vlan_id = isset($vlans_dict[$vlan]) ? $vlans_dict[$vlan] : 0;

            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
            d_echo("vlan $vlan mac $mac_address port ($dot1dBasePort) $port_id\n");
        }
    }
}

echo PHP_EOL;
