<?php

$continue = true;

// Build ifIndex to port_id dictionary
$ifIndex_dict = array();
foreach (dbFetchRows("SELECT `ifIndex`,`port_id` FROM `ports` WHERE `device_id` = ?", array($device['device_id'])) as $port_entry) {
    $ifIndex_dict[$port_entry['ifIndex']] = $port_entry['port_id'];
}

// Build dot1dBasePort to port_id dictionary
$portid_dict = array();

// Build a dictionary of vlans in database
$vlans_dict = array();
foreach (dbFetchRows("SELECT `vlan_id`, `vlan_vlan` from `vlans` WHERE `device_id` = ?", array($device['device_id'])) as $vlan_entry) {
    $vlans_dict[$vlan_entry['vlan_vlan']] = $vlan_entry['vlan_id'];
}

// Include all fdb-table discovery modules
$include_dir = 'includes/discovery/fdb-table';
require 'includes/include-dir.inc.php';

if ($continue) {
    // Build table of existing vlan/mac table
    $existing_fdbs = array();
    $sql_result = dbFetchRows("SELECT * FROM `ports_fdb` WHERE `device_id` = ?", array($device['device_id']));
    foreach ($sql_result as $entry) {
        $existing_fdbs[$entry['vlan_id']][$entry['mac_address']] = $entry;
    }
    // Insert to database
    foreach ($insert as $vlan_id => $mac_address_table) {
        foreach ($mac_address_table as $mac_address_entry => $value) {
            // If existing entry
            if ($existing_fdbs[$vlan_id][$mac_address_entry]) {
                unset($update_entry);

                // Look for columns that need to be updated
                $new_port = $insert[$vlan_id][$mac_address_entry]['port_id'];

                if ($existing_fdbs[$vlan_id][$mac_address_entry]['port_id'] != $new_port) {
                    $update_entry['port_id'] = $new_port;
                }

                if (! empty($update_entry)) {
                    dbUpdate($update_entry, 'ports_fdb', '`device_id` = ? AND `vlan_id` = ? AND `mac_address` = ?', array($device['device_id'], $vlan_id, $mac_address));
                }
                unset($existing_fdbs[$vlan_id][$mac_address_entry]);
            } else {
                $new_entry = array(
                    'port_id' => $value['port_id'],
                    'mac_address' => $mac_address_entry,
                    'vlan_id' => $vlan_id,
                    'device_id' => $device['device_id'],
                );

                dbInsert($new_entry, 'ports_fdb');
            }
        }
    }

    // Delete old entries from the database
    foreach ($existing_fdbs as $vlan_group => $mac_address_table) {
        foreach ($mac_address_table as $mac_address_entry => $value) {
            dbDelete('ports_fdb', '`port_id` = ? AND `mac_address` = ? AND `vlan_id` = ? and `device_id` = ?', array($value['port_id'], $value['mac_address'], $value['vlan_id'], $value['device_id']));
        }
    }
} // end if $continue
