<?php

if ($device['os_group'] == 'cisco') {
    echo "Cisco VLANs:\n";

    $native_vlans = snmpwalk_cache_oid($device, 'vlanTrunkPortNativeVlan', array(), 'CISCO-VTP-MIB');
    $native_vlans = snmpwalk_cache_oid($device, 'vmVlan', $native_vlans, 'CISCO-VLAN-MEMBERSHIP-MIB');

    // Not sure why we check for VTP, but this data comes from that MIB, so...
    $vtpversion = snmp_get($device, 'vtpVersion.0', '-OnvQ', 'CISCO-VTP-MIB');
    if ($vtpversion == '1' || $vtpversion == '2' || $vtpversion == '3' || $vtpversion == 'one' || $vtpversion == 'two' || $vtpversion == 'three' || $vtpversion == 'none') {
        // FIXME - can have multiple VTP domains.
        $vtpdomains = snmpwalk_cache_oid($device, 'vlanManagementDomains', array(), 'CISCO-VTP-MIB');
        $vlans      = snmpwalk_cache_twopart_oid($device, 'vtpVlanEntry', array(), 'CISCO-VTP-MIB');

        foreach ($vtpdomains as $vtpdomain_id => $vtpdomain) {
            echo 'VTP Domain '.$vtpdomain_id.' '.$vtpdomain['managementDomainName'].' ';
            foreach ($vlans[$vtpdomain_id] as $vlan_id => $vlan) {
                d_echo(" $vlan_id");
                if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
                    echo '.';
                } else {
                    dbInsert(array('device_id' => $device['device_id'], 'vlan_domain' => $vtpdomain_id, 'vlan_vlan' => $vlan_id, 'vlan_name' => $vlan['vtpVlanName'], 'vlan_type' => $vlan['vtpVlanType']), 'vlans');
                    echo '+';
                }
                $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;

                if (is_numeric($vlan_id) && ($vlan_id < 1002 || $vlan_id > 1005)) {
                    // Ignore reserved VLAN IDs
                    // get dot1dStpPortEntry within the vlan context
                    $vlan_device = array_merge($device, array('community' => $device['community'] . '@' . $vlan_id, 'context_name' => "vlan-$vlan_id"));
                    $tmp_vlan_data = snmpwalk_cache_oid($vlan_device, 'dot1dStpPortEntry', array(), 'BRIDGE-MIB');

                    // may need to fetch additional dot1dBasePortIfIndex mappings
                    $tmp_vlan_data = snmpwalk_cache_oid($vlan_device, 'dot1dBasePortIfIndex', $tmp_vlan_data, 'BRIDGE-MIB');
                    $vlan_data = array();
                    // flatten the array, use ifIndex instead of dot1dBasePortId
                    foreach ($tmp_vlan_data as $index => $array) {
                        if (isset($array['dot1dBasePortIfIndex'])) {
                            $base_to_index[$index] = $array['dot1dBasePortIfIndex'];
                            $index_to_base[$array['dot1dBasePortIfIndex']] = $index;
                        }
                        $vlan_data[$base_to_index[$index]] = $array;
                    }

                    $per_vlan_data[$vlan_id] = $vlan_data;
                }
            }
            echo PHP_EOL;

            // set all untagged vlans
            foreach ($native_vlans as $ifIndex => $data) {
                if (isset($data['vmVlan'])) {
                    $vlan_id = $data['vmVlan'];
                } else {
                    $vlan_id = $data['vlanTrunkPortNativeVlan'];
                }
                $base = $index_to_base[$ifIndex];
                echo "Vlan: $vlan_id tagged on $base (ifIndex $ifIndex)\n";
                $per_vlan_data[$vlan_id][$ifIndex]['untagged'] = 1;
            }
        }
    }
    echo PHP_EOL;
}
