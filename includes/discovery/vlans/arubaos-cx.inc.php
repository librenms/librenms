<?php

use App\Models\Vlan;
use LibreNMS\Enum\Severity;

echo "ArubaOS-CX VLANs:\n";

$vlans = SnmpQuery::walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticUntaggedPorts')->table(2);
$vlans = SnmpQuery::walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticEgressPorts')->table(2, $vlans);
$vlans = SnmpQuery::walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticName')->table(2, $vlans);

foreach ($vlans as $vlan_domain_id => $vlan_domains) {
    d_echo("Processing vlan domain ID: $vlan_domain_id");
    foreach ($vlan_domains as $vlan_id => $vlan) {
        d_echo("Processing vlan ID: $vlan_id");
        $vlan_name = empty($vlan['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticName']) ? "VLAN $vlan_id" : $vlan['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticName'];

        //try to get existing data from DB
        $vlanDB = Vlan::firstOrNew([
            'device_id' => $device['device_id'],
            'vlan_vlan' => $vlan_id,
        ], [
            'vlan_domain' => $vlan_domain_id,
            'vlan_name' => $vlan_name,
        ]);

        //vlan does not exist
        if (! $vlanDB->exists) {
            \App\Models\Eventlog::log("Vlan added: $vlan_id with name $vlan_name", $device['device_id'], 'vlan', Severity::Warning);
            d_echo("Vlan added: $vlan_id with name $vlan_name");
        }

        if ($vlanDB->vlan_name != $vlan_name) {
            $vlanDB->vlan_name = $vlan_name;
            \App\Models\Eventlog::log("Vlan changed: $vlan_id new name $vlan_name", $device['device_id'], 'vlan', Severity::Warning);
            d_echo("Vlan changed: $vlan_id new name $vlan_name");
        }

        $vlanDB->save();

        $device['vlans'][$vlan_domain_id][$vlan_id] = $vlan_id; //populate device['vlans'] with ID's

        //portmap for untagged ports
        $untagged_ids = q_bridge_bits2indices($vlan['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticUntaggedPorts'] ?? $vlan['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticUntaggedPorts'] ?? '');

        //portmap for members ports (might be tagged)
        $egress_ids = q_bridge_bits2indices($vlan['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticEgressPorts'] ?? $vlan['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticEgressPorts'] ?? '');

        foreach ($egress_ids as $port_id) {
            if (isset($base_to_index[$port_id])) {
                $ifIndex = $base_to_index[$port_id];
                $per_vlan_data[$vlan_id][$ifIndex]['untagged'] = (in_array($port_id, $untagged_ids) ? 1 : 0);
            }
        }
    }
}
