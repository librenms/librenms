<?php

if ($device['os_group'] == 'cisco') {
    echo 'Cisco VLANs : ';

    // Not sure why we check for VTP, but this data comes from that MIB, so...
    $vtpversion = snmp_get($device, 'vtpVersion.0', '-OnvQ', 'CISCO-VTP-MIB');
    if ($vtpversion == '1' || $vtpversion == '2' || $vtpversion == '3' || $vtpversion == 'one' || $vtpversion == 'two' || $vtpversion == 'three' || $vtpversion == 'none') {
        // FIXME - can have multiple VTP domains.
        $vtpdomains = snmpwalk_cache_oid($device, 'vlanManagementDomains', array(), 'CISCO-VTP-MIB');
        $vlans      = snmpwalk_cache_twopart_oid($device, 'vtpVlanEntry', array(), 'CISCO-VTP-MIB');

        foreach ($vtpdomains as $vtpdomain_id => $vtpdomain) {
            echo 'VTP Domain  '.$vtpdomain_id.' '.$vtpdomain['managementDomainName'].' ';
            foreach ($vlans[$vtpdomain_id] as $vlan_id => $vlan) {
                echo " $vlan_id";
                if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
                    echo '.';
                } else {
                    dbInsert(array('device_id' => $device['device_id'], 'vlan_domain' => $vtpdomain_id, 'vlan_vlan' => $vlan_id, 'vlan_name' => $vlan['vtpVlanName'], 'vlan_type' => $vlan['vtpVlanType']), 'vlans');
                    echo '+';
                }
                $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;
            }
        }
    }
    echo "\n";
}
