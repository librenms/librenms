<?php

// Pre-cache the existing state of VLANs for this device from the database
use LibreNMS\Config;

$vlans_db = [];
$vlans_db_raw = dbFetchRows('SELECT * FROM `vlans` WHERE `device_id` = ?', [$device['device_id']]);
foreach ($vlans_db_raw as $vlan_db) {
    $vlans_db[$vlan_db['vlan_domain']][$vlan_db['vlan_vlan']] = $vlan_db;
}
unset(
    $vlans_db_raw
);

// Create an empty array to record what VLANs we discover this session.
$device['vlans'] = [];
$per_vlan_data = [];  // fill this with data for each vlan
$valid_vlan_port = [];

// get a map of base port to ifIndex and the inverse
$base_to_index = [];
$tmp_base_indexes = snmpwalk_cache_oid($device, 'dot1dBasePortIfIndex', [], 'BRIDGE-MIB');
// flatten the array
foreach ($tmp_base_indexes as $index => $array) {
    $base_to_index[$index] = $array['dot1dBasePortIfIndex'];
}
$index_to_base = array_flip($base_to_index);

if (file_exists(Config::get('install_dir') . "/includes/discovery/vlans/{$device['os']}.inc.php")) {
    include Config::get('install_dir') . "/includes/discovery/vlans/{$device['os']}.inc.php";
}

if (empty($device['vlans']) === true) {
    require 'includes/discovery/vlans/q-bridge-mib.inc.php';
    require 'includes/discovery/vlans/cisco-vtp.inc.php';
}

// Fetch switchport <> VLAN relationships. This is DIRTY.
foreach ($device['vlans'] as $domain_id => $vlans) {
    foreach ($vlans as $vlan_id => $vlan) {
        // grab the populated data for this vlan
        $vlan_data = $per_vlan_data[$vlan_id];

        echo "VLAN $vlan_id \n";

        if ($vlan_data) {
            echo str_pad('dot1d id', 10) . str_pad('ifIndex', 10) . str_pad('Port Name', 25) . str_pad('Priority', 10) . str_pad('State', 15) . str_pad('Cost', 10) . "\n";
        }

        foreach ((array) $vlan_data as $ifIndex => $vlan_port) {
            $port = get_port_by_index_cache($device['device_id'], $ifIndex);
            echo str_pad($vlan_port_id, 10) . str_pad($ifIndex, 10) . str_pad($port['ifDescr'], 25) . str_pad($vlan_port['dot1dStpPortPriority'], 10) . str_pad($vlan_port['dot1dStpPortState'], 15) . str_pad($vlan_port['dot1dStpPortPathCost'], 10);

            $db_w = [
                'device_id' => $device['device_id'],
                'port_id'   => $port['port_id'],
                'vlan'      => $vlan_id,
            ];

            $db_a['baseport'] = $index_to_base[$ifIndex];
            $db_a['priority'] = isset($vlan_port['dot1dStpPortPriority']) ? $vlan_port['dot1dStpPortPriority'] : 0;
            $db_a['state'] = isset($vlan_port['dot1dStpPortState']) ? $vlan_port['dot1dStpPortState'] : 'unknown';
            $db_a['cost'] = isset($vlan_port['dot1dStpPortPathCost']) ? $vlan_port['dot1dStpPortPathCost'] : 0;
            $db_a['untagged'] = isset($vlan_port['untagged']) ? $vlan_port['untagged'] : 0;

            $from_db = dbFetchRow('SELECT * FROM `ports_vlans` WHERE device_id = ? AND port_id = ? AND `vlan` = ?', [$device['device_id'], $port['port_id'], $vlan_id]);

            if ($from_db['port_vlan_id']) {
                $db_id = $from_db['port_vlan_id'];
                dbUpdate($db_a, 'ports_vlans', '`port_vlan_id` = ?', [$db_id]);
                echo 'Updated';
            } else {
                $db_id = dbInsert(array_merge($db_w, $db_a), 'ports_vlans');
                echo 'Inserted';
            }
            $valid_vlan_port[] = $db_id;

            echo PHP_EOL;
        }//end foreach
    }//end foreach
}//end foreach

// remove non-existent vlans
foreach ($vlans_db as $domain_id => $vlans) {
    foreach ($vlans as $vlan_id => $vlan) {
        if (empty($device['vlans'][$domain_id][$vlan_id])) {
            dbDelete('vlans', '`device_id` = ? AND vlan_domain = ? AND vlan_vlan = ?', [$device['device_id'], $domain_id, $vlan_id]);
        }
    }
}

// remove non-existent port-vlan mappings
if (! empty($valid_vlan_port)) {
    $num = dbDelete('ports_vlans', '`device_id`=? AND `port_vlan_id` NOT IN ' . dbGenPlaceholders(count($valid_vlan_port)), array_merge([$device['device_id']], $valid_vlan_port));
    d_echo("Deleted $num vlan mappings\n");
}

unset($device['vlans']);
unset($base_to_index, $tmp_base_indexes, $index_to_base, $per_vlan_data, $valid_vlan_port, $num);
