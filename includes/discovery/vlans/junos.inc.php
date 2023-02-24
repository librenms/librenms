<?php

echo 'JUNIPER-VLAN-MIB VLANs: ';

$vlanversion = snmp_get($device, 'dot1qVlanVersionNumber.0', '-Oqv', 'IEEE8021-Q-BRIDGE-MIB');

if ($vlanversion == 'version1' || $vlanversion == '2') {
    echo "ver $vlanversion ";
    $vtpdomain_id = '1';
    $vlans = snmpwalk_cache_oid($device, 'jnxExVlanName', [], 'JUNIPER-VLAN-MIB');
    if (empty($vlans)) {
        $vlans = snmpwalk_cache_oid($device, 'jnxL2aldVlanName', [], 'JUNIPER-L2ALD-MIB');
        $vlan_tag = snmpwalk_cache_oid($device, 'jnxL2aldVlanTag', [], 'JUNIPER-L2ALD-MIB', null, ['-OQUs', '--hexOutputLength=0']);
        $untag = snmpwalk_cache_oid($device, 'jnxExVlanPortTagness', [], 'JUNIPER-VLAN-MIB', null, ['-OQeUs', '--hexOutputLength=0']);
        $tmp_tag = 'jnxL2aldVlanTag';
        $tmp_name = 'jnxL2aldVlanName';
    } else {
        $vlan_tag = snmpwalk_cache_oid($device, 'jnxExVlanTag', [], 'JUNIPER-VLAN-MIB', null, ['-OQUs', '--hexOutputLength=0']);
        $untag = snmpwalk_cache_oid($device, 'jnxExVlanPortTagness', [], 'JUNIPER-VLAN-MIB', null, ['-OQeUs', '--hexOutputLength=0']);
        $tmp_tag = 'jnxExVlanTag';
        $tmp_name = 'jnxExVlanName';
    }

    if (empty($untag)) {
        // If $untag is empty, device is based on Junipers ELS software
        $untag = snmpwalk_cache_oid($device, 'dot1qVlanStaticUntaggedPorts', [], 'Q-BRIDGE-MIB', null, ['-OQUs', '--hexOutputLength=0']);
        $taganduntag = snmpwalk_cache_oid($device, 'dot1qVlanStaticEgressPorts', [], 'Q-BRIDGE-MIB', null, ['-OQUs', '--hexOutputLength=0']);
        $vlan_tag = snmpwalk_cache_oid($device, 'jnxL2aldVlanTag', [], 'JUNIPER-L2ALD-MIB', null, ['-OQUs', '--hexOutputLength=0']);
        $tmp_tag = 'jnxL2aldVlanTag';
        $tmp_name = 'jnxL2aldVlanName';
        $temp_vlan = [];
        foreach ($vlan_tag as $key => $value) {
            $temp_vlan[$key] = $value['jnxL2aldVlanTag'];
        }
        //set all port vlan relationships to be tagged
        foreach ($taganduntag as $key => $taganduntag) {
            $vlan_index = array_search($key, $temp_vlan);
            $port_on_vlan = explode(',', $taganduntag['dot1qVlanStaticEgressPorts']);
            foreach ($port_on_vlan as $port) {
                $tagness_by_vlan_index[$vlan_index][$base_to_index[$port]]['tag'] = 0;
                unset($tagness_by_vlan_index[$vlan_index]['']);
            }
        }
        // correct all untagged ports to be untagged
        foreach ($untag as $key => $untag) {
            $vlan_index = array_search($key, $temp_vlan);
            $port_on_vlan = explode(',', $untag['dot1qVlanStaticUntaggedPorts']);
            foreach ($port_on_vlan as $port) {
                $tagness_by_vlan_index[$vlan_index][$base_to_index[$port]]['tag'] = 1;
                unset($tagness_by_vlan_index[$vlan_index]['']);
            }
        }
    } else {
        foreach ($untag as $key => $tagness) {
            $key = explode('.', $key);
            if ($tagness['jnxExVlanPortTagness'] == 2) {
                $tagness_by_vlan_index[$key[0]][$base_to_index[$key[1]]]['tag'] = 1;
            } else {
                $tagness_by_vlan_index[$key[0]][$base_to_index[$key[1]]]['tag'] = 0;
            }
        }
    }
    foreach ($vlans as $vlan_index => $vlan) {
        $vlan_id = $vlan_tag[$vlan_index][$tmp_tag];
        d_echo("VLAN --> $vlan_id");
        if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
            $vlan_data = $vlans_db[$vtpdomain_id][$vlan_id];
            if ($vlan_data['vlan_name'] != $vlan[$tmp_name]) {
                $vlan_upd['vlan_name'] = $vlan[$tmp_name];
                dbUpdate($vlan_upd, 'vlans', '`vlan_id` = ?', [$vlan_data['vlan_id']]);
                log_event("VLAN $vlan_id changed name {$vlan_data['vlan_name']} -> {$vlan[$tmp_name]} ", $device, 'vlan', 3, $vlan_data['vlan_id']);
                echo 'U';
            } else {
                echo '.';
            }
        } else {
            dbInsert([
                'device_id' => $device['device_id'],
                'vlan_domain' => $vtpdomain_id,
                'vlan_vlan' => $vlan_id,
                'vlan_name' => $vlan[$tmp_name],
                'vlan_type' => ['NULL'],
            ], 'vlans');
            echo '+';
        }
        $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;

        d_echo('');
        if (isset($tagness_by_vlan_index[$vlan_index])) {
            d_echo("JunOS: vlanID $vlan_id, index $vlan_index");

            foreach ($tagness_by_vlan_index[$vlan_index] as $ifIndex => $tag) {
                $f_portType = $tag['tag'] ? 'access' : 'trunk';

                d_echo("JunOS:  port-ifIndex $ifIndex - $f_portType port");

                $per_vlan_data[$vlan_id][$ifIndex]['untagged'] = $tag['tag'];
            }
        } else {
            d_echo('JunOS: No tag/untagged interfaces found for L2 associated' .
                   " with vlanID: $vlan_id - Index $vlan_index");
        }
    }
}
