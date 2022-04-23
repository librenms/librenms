<?php

echo 'RC-VLAN-MIB VLANs: ';

if ($device['os'] == 'boss') {
    $vtpdomain_id = '1';
    $vlans = snmpwalk_cache_oid($device, 'rcVlanName', [], 'RC-VLAN-MIB');
    $tagoruntag = snmpwalk_cache_oid($device, 'rcVlanPortMembers', [], 'RC-VLAN-MIB', null, ['-OQUs', '--hexOutputLength=0']);
    $port_pvids = snmpwalk_cache_oid($device, 'rcVlanPortDefaultVlanId', [], 'RC-VLAN-MIB');
    $port_mode = snmpwalk_cache_oid($device, 'rcVlanPortPerformTagging', [], 'RC-VLAN-MIB');

    foreach ($vlans as $vlan_id => $vlan) {
        d_echo(" $vlan_id");
        if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
            $vlan_data = $vlans_db[$vtpdomain_id][$vlan_id];
            if ($vlan_data['vlan_name'] != $vlan['rcVlanName']) {
                $vlan_upd['vlan_name'] = $vlan['rcVlanName'];
                dbUpdate($vlan_upd, 'vlans', '`vlan_id` = ?', [$vlan_data['vlan_id']]);
                log_event("VLAN $vlan_id changed name {$vlan_data['vlan_name']} -> {$vlan['rcVlanName']} ", $device, 'vlan', 3, $vlan_data['vlan_id']);
                echo 'U';
            } else {
                echo '.';
            }
        } else {
            dbInsert([
                'device_id' => $device['device_id'],
                'vlan_domain' => $vtpdomain_id,
                'vlan_vlan' => $vlan_id,
                'vlan_name' => $vlan['rcVlanName'],
                'vlan_type' => ['NULL'],
            ], 'vlans');
            echo '+';
        }
        $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;
        $egress_ids = q_bridge_bits2indices($tagoruntag[$vlan_id]['rcVlanPortMembers']);
        $untagged_ids = [];

        foreach ($port_pvids as $port => $port_num) {
            if ($port_num['rcVlanPortDefaultVlanId'] == $vlan_id &&
            ($port_mode[$port]['rcVlanPortPerformTagging'] == 'false' || $port_mode[$port]['rcVlanPortPerformTagging'] == 4)) {
                array_push($untagged_ids, $port);
            }
        }

        foreach ($egress_ids as $port_id) {
            $ifIndex = $base_to_index[$port_id - 1]; // -1 fixes off by one error
            $per_vlan_data[$vlan_id][$ifIndex]['untagged'] = (in_array($port_id - 1, $untagged_ids) ? 1 : 0); // -1 fixes off by one error
        }
    }
}
