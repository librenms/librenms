<?php

use App\Models\PortsFdb;
use App\Models\Vlan;
use LibreNMS\Config;

// Build a dictionary of vlans in database
$vlans_dict = [];
$dbRows = Vlan::where('device_id', $device['device_id'])->select('vlan_id', 'vlan_vlan')->get();
foreach ($dbRows as $vlan_entry) {
    $vlans_dict[$vlan_entry['vlan_vlan']] = $vlan_entry['vlan_id'];
}
$vlans_by_id = array_flip($vlans_dict);

// Build table of existing vlan/mac table
$existing_fdbs = [];
$dbRows = PortsFdb::where('device_id', $device['device_id'])->select('ports_fdb_id', 'port_id', 'mac_address', 'vlan_id')->get();
foreach ($dbRows as $entry) {
    $existing_fdbs[(int) $entry['vlan_id']][$entry['mac_address']] = $entry;
}

// populate $insert with data from device
$insert = [];
if (file_exists(Config::get('install_dir') . "/includes/discovery/fdb-table/{$device['os']}.inc.php")) {
    require Config::get('install_dir') . "/includes/discovery/fdb-table/{$device['os']}.inc.php";
} elseif ($device['os'] == 'ios' || $device['os'] == 'iosxe' || $device['os'] == 'nxos') {
    //ios,iosxe,nxos are all Cisco
    include Config::get('install_dir') . '/includes/discovery/fdb-table/ios.inc.php';
}

if (empty($insert)) {
    // Check generic Q-BRIDGE-MIB and BRIDGE-MIB
    include Config::get('install_dir') . '/includes/discovery/fdb-table/bridge.inc.php';
}

if (! empty($insert)) {
    $update_time_only = [];
    $now = \Carbon\Carbon::now();
    // synchronize with the database
    foreach ($insert as $vlan_id => $mac_address_table) {
        $vlan_name = $vlans_by_id[$vlan_id] ?? $vlan_id;
        echo " $vlan_name: ";

        foreach ($mac_address_table as $mac_address_entry => $entry) {
            if (isset($existing_fdbs[$vlan_id][$mac_address_entry])) {
                $new_port = $entry['port_id'];
                $port_fdb_id = $existing_fdbs[$vlan_id][$mac_address_entry]['ports_fdb_id'];

                // Sometimes new_port ends up as 0 if we didn't get a complete dot1dBasePort
                // dictionary from BRIDGE-MIB - don't write a 0 over a previously known port
                if ($existing_fdbs[$vlan_id][$mac_address_entry]['port_id'] != $new_port && $new_port != 0) {
                    PortsFdb::where('ports_fdb_id', $port_fdb_id)
                        ->update(['port_id' => $new_port, 'updated_at' => $now]);
                    echo 'U';
                } else {
                    $update_time_only[] = $port_fdb_id;
                    echo '.';
                }
                unset($existing_fdbs[$vlan_id][$mac_address_entry]);
            } else {
                PortsFdb::create([
                    'port_id' => $entry['port_id'] ?? '',
                    'mac_address' => $mac_address_entry,
                    'vlan_id' => $vlan_id,
                    'device_id' => $device['device_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                echo '+';
            }
        }

        echo PHP_EOL;
    }

    PortsFdb::whereIntegerInRaw('ports_fdb_id', $update_time_only)->update(['updated_at' => $now]);

    //We do not delete anything here, as daily.sh will take care of the cleaning.
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
    $entries,
    $update_time_only,
    $now,
    $dbRows
);
