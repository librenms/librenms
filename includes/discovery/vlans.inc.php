<?php

// Pre-cache the existing state of VLANs for this device from the database
$vlans_db_raw = dbFetchRows('SELECT * FROM `vlans` WHERE `device_id` = ?', array($device['device_id']));
foreach ($vlans_db_raw as $vlan_db) {
    $vlans_db[$vlan_db['vlan_domain']][$vlan_db['vlan_vlan']] = $vlan_db;
}

// Create an empty array to record what VLANs we discover this session.
$device['vlans'] = array();
$valid_vlan_port_ids = array();

require 'includes/discovery/vlans/q-bridge-mib.inc.php';
require 'includes/discovery/vlans/cisco-vtp.inc.php';

// Fetch switchport <> VLAN relationships. This is DIRTY.
foreach ($device['vlans'] as $domain_id => $vlans) {
    foreach ($vlans as $vlan_id => $vlan) {
        // FIXME - do this only when vlan type == ethernet?
        if (is_numeric($vlan_id) && ($vlan_id < 1002 || $vlan_id > 1005)) {
            // Ignore reserved VLAN IDs
            if ($device['os_group'] == 'cisco' || $device['os'] == 'ios') {
                // This shit only seems to work on IOS
                // Probably does not work with snmpv3. I have no real idea about what this code is really doing
                $vlan_device = array_merge($device, array('community' => $device['community'].'@'.$vlan_id));
                $vlan_data   = snmpwalk_cache_oid($vlan_device, 'dot1dStpPortEntry', array(), 'BRIDGE-MIB:Q-BRIDGE-MIB');
                $vlan_data   = snmpwalk_cache_oid($vlan_device, 'dot1dBasePortEntry', $vlan_data, 'BRIDGE-MIB:Q-BRIDGE-MIB');
            } elseif (isset($qbridge_data)) {
                $vlan_data = $qbridge_data[$vlan_id];
            }

            echo "VLAN $vlan_id \n";

            if ($vlan_data) {
                echo str_pad('dot1d id', 10).str_pad('ifIndex', 10).str_pad('Port Name', 25).str_pad('Priority', 10).str_pad('State', 15).str_pad('Cost', 10)."\n";
            }

            foreach ($vlan_data as $vlan_port_id => $vlan_port) {
                $port = get_port_by_index_cache($device, $vlan_port['dot1dBasePortIfIndex']);
                echo str_pad($vlan_port_id, 10).str_pad($vlan_port['dot1dBasePortIfIndex'], 10).str_pad($port['ifDescr'], 25).str_pad($vlan_port['dot1dStpPortPriority'], 10).str_pad($vlan_port['dot1dStpPortState'], 15).str_pad($vlan_port['dot1dStpPortPathCost'], 10);

                $db_w = array(
                    'device_id' => $device['device_id'],
                    'port_id'   => $port['port_id'],
                    'vlan'      => $vlan_id,
                );

                $db_a['baseport'] = $vlan_port_id;
                $db_a['priority'] = isset($vlan_port['dot1dStpPortPriority']) ? $vlan_port['dot1dStpPortPriority'] : 0;
                $db_a['state']    = isset($vlan_port['dot1dStpPortState']) ? $vlan_port['dot1dStpPortState'] : 'unknown';
                $db_a['cost']     = isset($vlan_port['dot1dStpPortPathCost']) ? $vlan_port['dot1dStpPortPathCost'] : 0;
                $db_a['untagged'] = isset($vlan_port['untagged']) ? $vlan_port['untagged'] : 0;

                $from_db = dbFetchRow('SELECT * FROM `ports_vlans` WHERE device_id = ? AND port_id = ? AND `vlan` = ?', array($device['device_id'], $port['port_id'], $vlan_id));

                if ($from_db['port_vlan_id']) {
                    $db_id = $from_db['port_vlan_id'];
                    dbUpdate($db_a, 'ports_vlans', '`port_vlan_id` = ?', array($db_id));
                    echo 'Updated';
                } else {
                    $db_id = dbInsert(array_merge($db_w, $db_a), 'ports_vlans');
                    echo 'Inserted';
                }
                $valid_vlan_port_ids[] = $db_id;

                echo PHP_EOL;
            }//end foreach
        }//end if
    }//end foreach
}//end foreach

foreach ($vlans_db as $domain_id => $vlans) {
    foreach ($vlans as $vlan_id => $vlan) {
        if (empty($device['vlans'][$domain_id][$vlan_id])) {
            dbDelete('vlans', '`device_id` = ? AND vlan_domain = ? AND vlan_vlan = ?', array($device['device_id'], $domain_id, $vlan_id));
        }
    }
}

// remove non-existent port-vlan mappings
$num = dbDelete('ports_vlans', '`device_id`=? AND `port_vlan_id` NOT IN ('.join(',', $valid_vlan_port).')', array($device['device_id']));
d_echo("Deleted $num vlan mappings\n");

unset($device['vlans']);
