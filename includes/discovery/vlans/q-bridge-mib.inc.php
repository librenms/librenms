<?php
echo 'IEEE8021-Q-BRIDGE-MIB VLANs: ';

$vlanversion = snmp_get($device, 'dot1qVlanVersionNumber.0', '-Oqv', 'IEEE8021-Q-BRIDGE-MIB');

if ($vlanversion == 'version1' || $vlanversion == '2') {
    echo "ver $vlanversion ";

    $qbridge_data = array();
    $vtpdomain_id = '1';
    $vlans        = snmpwalk_cache_oid($device, 'dot1qVlanStaticName', array(), 'Q-BRIDGE-MIB');
    $tagoruntag   = snmpwalk_cache_oid($device, 'dot1qVlanCurrentEgressPorts', array(), 'Q-BRIDGE-MIB', null, '-OQUs --hexOutputLength=0');
    $untag        = snmpwalk_cache_oid($device, 'dot1qVlanCurrentUntaggedPorts', array(), 'Q-BRIDGE-MIB', null, '-OQUs --hexOutputLength=0');
    $base_indexes = snmpwalk_cache_oid($device, 'dot1dBasePortIfIndex', array(), 'BRIDGE-MIB');

    foreach ($vlans as $vlan_id => $vlan) {
        d_echo(" $vlan_id");
        if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
            echo '.';
        } else {
            dbInsert(array(
                'device_id' => $device['device_id'],
                'vlan_domain' => $vtpdomain_id,
                'vlan_vlan' => $vlan_id,
                'vlan_name' => $vlan['dot1qVlanStaticName'],
                'vlan_type' => array('NULL')
            ), 'vlans');
            echo '+';
        }

        $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;

        $id = "0.$vlan_id";
        $untagged_indexes = q_bridge_bits2indices($untag[$id]['dot1qVlanCurrentUntaggedPorts']);
        $egress_indexes = q_bridge_bits2indices($tagoruntag[$id]['dot1qVlanCurrentEgressPorts']);

        foreach ($egress_indexes as $port_index) {
            $qbridge_data[$vlan_id][$port_index] = $base_indexes[$port_index];
            $qbridge_data[$vlan_id][$port_index]['untagged'] = (in_array($port_index, $untagged_indexes) ? 1 : 0);
        }
    }
}
echo PHP_EOL;
