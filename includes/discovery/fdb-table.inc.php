<?php

use LibreNMS\Discovery\FdbTable;

// Build a dictionary of vlans in database
$vlans_dict = [];
foreach (dbFetchRows('SELECT `vlan_id`, `vlan_vlan` from `vlans` WHERE `device_id` = ?', [$device['device_id']]) as $vlan_entry) {
    $vlans_dict[$vlan_entry['vlan_vlan']] = $vlan_entry['vlan_id'];
}
$vlans_by_id = array_flip($vlans_dict);

// Build table of existing vlan/mac table
$existing_fdbs = [];
$sql_result = dbFetchRows('SELECT * FROM `ports_fdb` WHERE `device_id` = ?', [$device['device_id']]);
foreach ($sql_result as $entry) {
    $existing_fdbs[(int) $entry['vlan_id']][$entry['mac_address']] = $entry;
}

$insert = []; // populate $insert with database entries
if (file_exists(base_path("includes/discovery/fdb-table/{$device['os']}.inc.php"))) {
    require base_path("includes/discovery/fdb-table/{$device['os']}.inc.php");
} elseif ($device['os'] == 'ios' || $device['os'] == 'iosxe' || $device['os'] == 'nxos') {
    //ios,iosxe,nxos are all Cisco
    include base_path('includes/discovery/fdb-table/ios.inc.php');
}

if (empty($insert)) {
    // Check generic Q-BRIDGE-MIB and BRIDGE-MIB
    include base_path('includes/discovery/fdb-table/bridge.inc.php');
}

FdbTable::sync($device['device_id'], $insert, $existing_fdbs, $vlans_by_id);

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
