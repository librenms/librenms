<?php

echo 'IEEE8021-Q-BRIDGE-MIB VLANs: ';

$vlanversion = snmp_get($device, 'dot1qVlanVersionNumber.0', '-Oqv', 'IEEE8021-Q-BRIDGE-MIB');

if ($vlanversion == 'version1' || $vlanversion == '2') {
    echo "ver $vlanversion ";

    $vtpdomain_id = '1';
    $vlans = snmpwalk_cache_oid($device, 'dot1qVlanStaticName', [], 'Q-BRIDGE-MIB');
    $tagoruntag = snmpwalk_cache_oid($device, 'dot1qVlanCurrentEgressPorts', [], 'Q-BRIDGE-MIB', null, ['-OQUs', '--hexOutputLength=0']);
    $untag = snmpwalk_cache_oid($device, 'dot1qVlanCurrentUntaggedPorts', [], 'Q-BRIDGE-MIB', null, ['-OQUs', '--hexOutputLength=0']);

    if ($tagoruntag == $null && $untag == $null) {
        // if dot1qVlanCurrentTable doesn't exist then use the dot1qVlanStaticTable
        $untaggedports = 'dot1qVlanStaticUntaggedPorts';
        $tagoruntagports = 'dot1qVlanStaticEgressPorts';
        $untag = snmpwalk_cache_oid($device, $untaggedports, [], 'Q-BRIDGE-MIB', null, ['-OQUs', '--hexOutputLength=0']);
        $tagoruntag = snmpwalk_cache_oid($device, $tagoruntagports, [], 'Q-BRIDGE-MIB', null, ['-OQUs', '--hexOutputLength=0']);
    } else {
        $untaggedports = 'dot1qVlanCurrentUntaggedPorts';
        $tagoruntagports = 'dot1qVlanCurrentEgressPorts';

        // drop dot1qVlanTimeMark, we don't care about it
        $tagoruntag = array_reduce(array_keys($tagoruntag), function ($result, $key) use ($tagoruntag) {
            [, $new_key] = explode('.', $key);
            $result[$new_key] = $tagoruntag[$key];

            return $result;
        }, []);
        $untag = array_reduce(array_keys($untag), function ($result, $key) use ($untag) {
            [, $new_key] = explode('.', $key);
            $result[$new_key] = $untag[$key];

            return $result;
        }, []);
    }

    foreach ($vlans as $vlan_id => $vlan) {
        d_echo(" $vlan_id");
        if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
            $vlan_data = $vlans_db[$vtpdomain_id][$vlan_id];
            if ($vlan_data['vlan_name'] != $vlan['dot1qVlanStaticName']) {
                $vlan_upd['vlan_name'] = $vlan['dot1qVlanStaticName'];
                dbUpdate($vlan_upd, 'vlans', '`vlan_id` = ?', [$vlan_data['vlan_id']]);
                log_event("VLAN $vlan_id changed name {$vlan_data['vlan_name']} -> {$vlan['dot1qVlanStaticName']} ", $device, 'vlan', 3, $vlan_data['vlan_id']);
                echo 'U';
            } else {
                echo '.';
            }
        } else {
            dbInsert([
                'device_id' => $device['device_id'],
                'vlan_domain' => $vtpdomain_id,
                'vlan_vlan' => $vlan_id,
                'vlan_name' => $vlan['dot1qVlanStaticName'],
                'vlan_type' => ['NULL'],
            ], 'vlans');
            echo '+';
        }

        $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;

        $untagged_ids = q_bridge_bits2indices($untag[$vlan_id][$untaggedports]);
        $egress_ids = q_bridge_bits2indices($tagoruntag[$vlan_id][$tagoruntagports]);

        foreach ($egress_ids as $port_id) {
            if (isset($base_to_index[$port_id])) {
                $ifIndex = $base_to_index[$port_id];
                $per_vlan_data[$vlan_id][$ifIndex]['untagged'] = (in_array($port_id, $untagged_ids) ? 1 : 0);
            }
        }
    }
}
echo PHP_EOL;
