<?php
require_once 'includes/discovery/vlans/vlan_functions.inc.php';

echo 'IEEE8021-Q-BRIDGE-MIB VLANs : ';

$vlanversion = snmp_get($device, 'dot1qVlanVersionNumber.0', '-Oqv', 'IEEE8021-Q-BRIDGE-MIB');

if ($vlanversion == 'version1' || $vlanversion == '2') {
    echo "VLAN $vlanversion ";

    $vtpdomain_id = '1';
    $vlans        = snmpwalk_cache_oid($device, 'dot1qVlanStaticName', array(), 'Q-BRIDGE-MIB');
    $tagoruntag   = snmpwalk_cache_oid($device, 'dot1qVlanCurrentEgressPorts', array(), 'Q-BRIDGE-MIB', null, '-OQUs --hexOutputLength=0');
    $untag        = snmpwalk_cache_oid($device, 'dot1qVlanCurrentUntaggedPorts', array(), 'Q-BRIDGE-MIB', null, '-OQUs --hexOutputLength=0');

    foreach ($vlans as $vlan_id => $vlan) {
        echo " $vlan_id";
        if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
            echo '.';
        } else {
            dbInsert(array('device_id' => $device['device_id'], 'vlan_domain' => $vtpdomain_id, 'vlan_vlan' => $vlan_id, 'vlan_name' => $vlan['dot1qVlanStaticName'], 'vlan_type' => array('NULL')), 'vlans');
            echo '+';
        }

        $key = "0.$vlan_id";

        $untagged_port_indices = q_bridge_bits2indices($untag[$key]['dot1qVlanCurrentUntaggedPorts']);
        $tagoruntag_indices = q_bridge_bits2indices($tagoruntag[$key]['dot1qVlanCurrentEgressPorts']);
        $tagged_port_indices = array_diff($tagoruntag_indices, $untagged_port_indices);

        foreach ($tagged_port_indices as $port_ifindex) {
            $port = get_port_by_index_cache($device, $port_ifindex);
            echo str_pad("q-bridge ", 10).str_pad($port_ifindex, 10).str_pad($port['ifDescr'], 25).str_pad("n/a", 10).str_pad("n/a", 15).str_pad("n/a", 10);

            $db_w = array(
                'device_id' => $device['device_id'],
                'port_id'   => $port['port_id'],
                'vlan'      => $vlan_id,
            );

            $db_a['baseport'] = $port_ifindex;
            $db_a['priority'] = 0;
            $db_a['state']    = 'unknown';
            $db_a['cost']     = 0;

            $from_db = dbFetchRow('SELECT * FROM `ports_vlans` WHERE device_id = ? AND port_id = ? AND `vlan` = ?', array($device['device_id'], $port['port_id'], $vlan_id));

            if ($from_db['port_vlan_id']) {
                dbUpdate($db_a, 'ports_vlans', '`port_vlan_id` = ?', array($from_db['port_vlan_id']));
                echo 'Updated';
            } else {
                dbInsert(array_merge($db_w, $db_a), 'ports_vlans');
                echo 'Inserted';
            }

            echo "\n";
        }//end foreach
    }
}

echo "\n";
