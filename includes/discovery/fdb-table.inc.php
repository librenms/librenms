<?php

// Build a dictionary of vlans in database
use LibreNMS\Config;

$vlans_dict = array();
foreach (dbFetchRows("SELECT `vlan_id`, `vlan_vlan` from `vlans` WHERE `device_id` = ?", array($device['device_id'])) as $vlan_entry) {
    $vlans_dict[$vlan_entry['vlan_vlan']] = $vlan_entry['vlan_id'];
}
$vlans_by_id = array_flip($vlans_dict);

// Build table of existing vlan/mac table
$existing_fdbs = array();
$sql_result = dbFetchRows("SELECT * FROM `ports_fdb` WHERE `device_id` = ?", array($device['device_id']));
foreach ($sql_result as $entry) {
    $existing_fdbs[(int)$entry['vlan_id']][$entry['mac_address']] = $entry;
}

$insert = []; // populate $insert with database entries
if (file_exists(Config::get('install_dir') . "/includes/discovery/fdb-table/{$device['os']}.inc.php")) {
    require Config::get('install_dir') . "/includes/discovery/fdb-table/{$device['os']}.inc.php";
} elseif ($device['os'] == 'ios' || $device['os'] == 'iosxe'|| $device['os'] == 'nxos') {
    //ios,iosxe,nxos are all Cisco
    include Config::get('install_dir') . '/includes/discovery/fdb-table/ios.inc.php';
}

if (empty($insert)) {
    // Check generic Q-BRIDGE-MIB and BRIDGE-MIB
    include Config::get('install_dir') . '/includes/discovery/fdb-table/bridge.inc.php';
}

if (!empty($insert)) {
    // synchronize with the database
    foreach ($insert as $vlan_id => $mac_address_table) {
        echo " {$vlans_by_id[$vlan_id]}: ";

        foreach ($mac_address_table as $mac_address_entry => $entry) {
            if ($existing_fdbs[$vlan_id][$mac_address_entry]) {
                $new_port = $entry['port_id'];

                if ($existing_fdbs[$vlan_id][$mac_address_entry]['port_id'] != $new_port) {
                    $port_fdb_id = $existing_fdbs[$vlan_id][$mac_address_entry]['ports_fdb_id'];
                    dbUpdate(
                        array('port_id' => $new_port, 'updated_at' => array('NOW()'),),
                        'ports_fdb',
                        '`device_id` = ? AND `vlan_id` = ? AND `mac_address` = ?',
                        array($device['device_id'], $vlan_id, $mac_address_entry)
                    );
                    echo 'U';
                } else {
                    dbUpdate(
                        array('updated_at' => array('NOW()'),), //we need to do this unless we use Eloquent "update" method
                        'ports_fdb',
                        '`device_id` = ? AND `vlan_id` = ? AND `mac_address` = ?',
                        array($device['device_id'], $vlan_id, $mac_address_entry)
                    );
                    echo '.';
                }
                unset($existing_fdbs[$vlan_id][$mac_address_entry]);
            } else {
                $new_entry = array(
                    'port_id' => $entry['port_id'],
                    'mac_address' => $mac_address_entry,
                    'vlan_id' => $vlan_id,
                    'device_id' => $device['device_id'],
                    'created_at' => array('NOW()'), //we need to do this unless we use Eloquent "create" method
                    'updated_at' => array('NOW()'), //we need to do this unless we use Eloquent "update" method
                );

                dbInsert($new_entry, 'ports_fdb');
                echo '+';
            }
        }

        echo PHP_EOL;
    }

    //We do not delete anything here, as daily.sh will take care of the cleaning.

    // Delete old entries from the database
}

unset(
    $vlan_entry,
    $vlans_by_id,
    $existing_fdbs,
    $portid_dict,
    $vlans_dict,
    $insert,
    $sql_result,
    $vlans,
    $port,
    $fdbPort_table,
    $entries
);
