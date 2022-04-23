<?php
/**
 * q-bridge-mib.inc.php
 *
 * LibreNMS vlan discovery module for Q-BRIDGE-MIB
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
 *
 * @author     peca.nesovanovic@sattrakt.com
 */

use App\Models\Vlan;

echo 'IEEE8021-Q-BRIDGE-MIB VLANs: ';

$vlanversion = snmp_get($device, 'dot1qVlanVersionNumber.0', '-Oqv', 'IEEE8021-Q-BRIDGE-MIB');

if ($vlanversion == 'version1' || $vlanversion == '2') {
    echo "ver $vlanversion ";

    $vtpdomain_id = '1';

    // fetch vlan data
    $vlans = SnmpQuery::walk('Q-BRIDGE-MIB::dot1qVlanCurrentUntaggedPorts')->table(2);
    $vlans = SnmpQuery::walk('Q-BRIDGE-MIB::dot1qVlanCurrentEgressPorts')->table(2, $vlans);
    if (empty($vlans)) {
        // fall back to static
        $vlans = SnmpQuery::walk('Q-BRIDGE-MIB::dot1qVlanStaticUntaggedPorts')->table(1, $vlans);
        $vlans = SnmpQuery::walk('Q-BRIDGE-MIB::dot1qVlanStaticEgressPorts')->table(1, $vlans);
    } else {
        // collapse timefilter from dot1qVlanCurrentTable results to only the newest
        $vlans = array_reduce($vlans, function ($result, $time_data) {
            foreach ($time_data as $vlan_id => $vlan_data) {
                $result[$vlan_id] = isset($result[$vlan_id]) ? array_merge($result[$vlan_id], $vlan_data) : $vlan_data;
            }

            return $result;
        }, []);
    }

    $vlans = SnmpQuery::walk('Q-BRIDGE-MIB::dot1qVlanStaticName')->table(1, $vlans);

    foreach ($vlans as $vlan_id => $vlan) {
        d_echo('Processing vlan ID: ' . $vlan_id);
        $vlan_name = empty($vlan['Q-BRIDGE-MIB::dot1qVlanStaticName']) ? "VLAN $vlan_id" : $vlan['Q-BRIDGE-MIB::dot1qVlanStaticName'];

        //try to get existing data from DB
        $vlanDB = Vlan::firstOrNew([
            'device_id' => $device['device_id'],
            'vlan_vlan' => $vlan_id,
        ], [
            'vlan_domain' => $vtpdomain_id,
            'vlan_name' => $vlan_name,
        ]);

        //vlan does not exist
        if (! $vlanDB->exists) {
            Log::event("Vlan added: $vlan_id with name $vlan_name ", $device['device_id'], 'vlan', 4);
        }

        if ($vlanDB->vlan_name != $vlan_name) {
            $vlanDB->vlan_name = $vlan_name;
            Log::event("Vlan changed: $vlan_id new name $vlan_name", $device['device_id'], 'vlan', 4);
        }

        $vlanDB->save();

        $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id; //populate device['vlans'] with ID's

        //portmap for untagged ports
        $untagged_ids = q_bridge_bits2indices($vlan['Q-BRIDGE-MIB::dot1qVlanCurrentUntaggedPorts'] ?? $vlan['Q-BRIDGE-MIB::dot1qVlanStaticUntaggedPorts']);
        //portmap for members ports (might be tagged)
        $egress_ids = q_bridge_bits2indices($vlan['Q-BRIDGE-MIB::dot1qVlanCurrentEgressPorts'] ?? $vlan['Q-BRIDGE-MIB::dot1qVlanStaticEgressPorts']);
        foreach ($egress_ids as $port_id) {
            if (isset($base_to_index[$port_id])) {
                $ifIndex = $base_to_index[$port_id];
                $per_vlan_data[$vlan_id][$ifIndex]['untagged'] = (in_array($port_id, $untagged_ids) ? 1 : 0);
            }
        }
    }
}
echo PHP_EOL;
