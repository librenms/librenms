<?php

echo 'IEEE8021-Q-BRIDGE-MIB VLANs : ';

$vlanversion = snmp_get($device, 'dot1qVlanVersionNumber.0', '-Oqv', 'IEEE8021-Q-BRIDGE-MIB');

if ($vlanversion == 'version1' || $vlanversion == '2') {
    echo "VLAN $vlanversion ";

    $vtpdomain_id = '1';
    $vlans        = snmpwalk_cache_oid($device, 'dot1qVlanStaticName', array(), 'IEEE8021-Q-BRIDGE-MIB');

    foreach ($vlans as $vlan_id => $vlan) {
        echo " $vlan_id";
        if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
            echo '.';
        } else {
            dbInsert(array('device_id' => $device['device_id'], 'vlan_domain' => $vtpdomain_id, 'vlan_vlan' => $vlan_id, 'vlan_name' => $vlan['dot1qVlanStaticName'], 'vlan_type' => array('NULL')), 'vlans');
            echo '+';
        }

        $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;
    }
}

echo "\n";
