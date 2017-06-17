<?php

$os_filename = 'includes/discovery/fdb-table/' . $device['os'] . '.inc.php';

if (is_file($os_filename)) {
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
    $vlans_by_id = array_flip($vlans_dict);

    // Build table of existing vlan/mac table
    $existing_fdbs = array();
    $sql_result = dbFetchRows("SELECT * FROM `ports_fdb` WHERE `device_id` = ?", array($device['device_id']));
    foreach ($sql_result as $entry) {
        $existing_fdbs[$entry['vlan_id']][$entry['mac_address']] = $entry;
    }

    // Include all fdb-table discovery modules
    $insert = array();
    include $os_filename;

    $valid_fdb = array();

    // synchronize with the database
    foreach ($insert as $vlan_id => $mac_address_table) {
        echo " {$vlans_by_id[$vlan_id]}: ";

        foreach ($mac_address_table as $mac_address_entry => $entry) {
            if ($existing_fdbs[$vlan_id][$mac_address_entry]) {
                $new_port = $entry['port_id'];

                if ($existing_fdbs[$vlan_id][$mac_address_entry]['port_id'] != $new_port) {
                    $port_fdb_id = $existing_fdbs[$vlan_id][$mac_address_entry]['ports_fdb_id'];
                    $valid_fdb[] = $port_fdb_id;

                    dbUpdate(
                        array('port_id' => $new_port),
                        'ports_fdb',
                        '`ports_fdb_id` = ?',
                        array($port_fdb_id)
                    );
                    echo 'U';
                } else {
                    echo '.';
                }
                unset($existing_fdbs[$vlan_id][$mac_address_entry]);
            } else {
                $new_entry = array(
                    'port_id' => $entry['port_id'],
                    'mac_address' => $mac_address_entry,
                    'vlan_id' => $vlan_id,
                    'device_id' => $device['device_id'],
                );

                $valid_fdb = dbInsert($new_entry, 'ports_fdb');
                echo '+';
            }
        }

        foreach ($existing_fdbs[$vlan_id] as $entry) {
            dbDelete('ports_fdb', '`ports_fdb_id` = ?', array($entry['ports_fdb_id']));
            echo '-';
        }
        echo PHP_EOL;
    }

    unset($existing_fdbs, $ifIndex_dict, $portid_dict, $vlans_dict);
}
