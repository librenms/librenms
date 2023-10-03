<?php

use App\Models\Vlan;
use App\Models\PortVlan;
use App\Models\Port;

echo 'CIENA-WWP-LEOS-VLAN-TAG-MIB VLANs: ';

$vlans_name = snmpwalk_cache_oid($device, 'wwpLeosVlanName', [], 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
$vlans_port = snmpwalk_cache_oid($device, 'wwpLeosVlanMemberTagId', [], 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
$ports_to_vlans = snmpwalk_cache_oid($device, 'wwpLeosVlanTagMemberEntry', [], 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
$vlan_mapping = [];
$vlanid_to_port = [];
$tmp_tag = 'wwpLeosVlanMemberTagId';
$tmp_name = 'wwpLeosVlanName';

foreach ($vlans_name as $vlans_key => $vlans_value) {
    foreach ($vlans_value as $vlan_item) {
        $vlan_mapping["$vlans_key"] = "$vlan_item";
        echo "\nthe key: $vlans_key\n";
        echo "\nthe item: $vlan_item\n";
        $vlan_mapping_rev["$vlans_item"] = "$vlan_key";
    }
}

foreach ($vlans_port as $port_index => $vlantoport) {
    $portindex_array = explode('.', $port_index);
    $vlanid_to_port["$port_index"] = "$portindex_array[2]";
    echo "\nportindex: {$portindex_array[2]}\n";
    d_echo('port index: ' . $port_index);
}

echo "\nVID to Port Mapping";
foreach ($vlanid_to_port as $vlan_port_key => $vlan_port_key_value) {
    $port_attr = explode('.', $vlan_port_key, 3);
    echo "\nport number is: {$port_attr[1]}\n";
    echo "\nVLAN ID is: {$vlan_port_key_value}\n";

    $port_to_dot1d = '1000' . $port_attr[1];
//     var_dump($vlan_mapping_rev);
//     echo "\nVLAN name is: {$vlan_mapping_rev}\n";
    $port_vlan_map['port'] = (int) $port_to_dot1d;
    $port_vlan_map['vlan'] = (int) $port_attr[0];
//     $port_vlan_map['port'] = $port_to_dot1d;
//     $port_vlan_map['vlan'] = $port_attr[0];

//     d_echo([$port_vlan_map['port']]);
     $db_a['baseport'] = (int) $port_attr[1];
//     $db_a['priority'] = '0';
//     $db_a['state'] = 'unknown';
//     $db_a['cost'] = '0';
//     $db_a['untagged'] = '0';

//     $from_db_portid_ifIndex = PortVlan::select('select port_id from ports WHERE ports.device_id = :pdi AND ifIndex = :ifi', ['pdi' => [$device['device_id']], ['ifi' => $port_vlan_map['port']]]);

//     $from_db_portid_ifIndex = PortVlan::select('port_id')
//         ->where('device_id', $device['device_id'])
//         ->where('ifIndex', $port_vlan_map['port'])
//         ->get();

//         d_echo($from_db_portid_ifIndex);

//     $from_db_portid_ifIndex = Vlans::database()->query('SELECT `port_id` from ports WHERE ports.device_id = ? AND ifIndex = ?', [$device['device_id'], $port_vlan_map['port']]);

     //$from_db_portid_ifIndex = PortVlan::select('port_id')
     //   ->where('device_id', $device['device_id'])
     //   ->where('ifIndex', $port_vlan_map['port'])
     //   ->get();
     //   d_echo('Process vlan IDs from db: ' . $from_db_portid_ifIndex);


//get port id for vlan query
     $from_db_port_id = Port::select('port_id')
        ->where('device_id', $device['device_id'])
        ->where('ifIndex', $port_vlan_map['port'])
        ->get();
	$portid_from_db = explode(':', $from_db_port_id);
        d_echo('Processing port ID from db: ' . $portid_from_db[1]);
//     {"port_id":3455}

 //vlan create works
//  Vlan::create( [
//      'device_id' => $device['device_id'],
//      'vlan_vlan'      => $port_vlan_map['vlan'],
//      'vlan_name' => $vlan_mapping[$port_attr[0]],
//  ]);
// vlan create works



    $from_db = PortVlan::select('*')
        ->where('device_id', $device['device_id'])
        ->where('port_id', (int) $portid_from_db[1])
        ->where('vlan', $port_vlan_map['vlan'])
        ->get();
    d_echo('Processing all device ids ports and vlans from db: ' . $from_db);

    if (is_array([$from_db])) {
        d_echo('does it need an update...');
	$vlan_id_check = Vlan::select(array('vlan_name','vlan_id'))
	  ->where('device_id', $device['device_id'])
          ->where('vlan_vlan', $port_vlan_map['vlan'])
          ->get();
          d_echo('vlan id check ' . $vlan_id_check);
          $vlan_strip = explode(',', $vlan_id_check[0]);
          $vlan_parsed = explode(':', $vlan_strip[0]);
          $vlan_cleaned = preg_replace("/\W|_/", '', $vlan_parsed[1]);
          d_echo('vlan strip is ' . $vlan_strip[0]);
          d_echo('vlan to be parsed is ' . $vlan_parsed[1]);
	  d_echo('vlan name is ' . $vlan_cleaned);
	  d_echo('vlan current listed ' . $vlan_mapping[$port_attr[0]]);

          $vlan_id_strip = explode(',', $vlan_id_check);
          $vlan_id_parsed = explode(':', $vlan_id_strip[1]);
          $vlan_id_cleaned = preg_replace("/\W|_/", '', $vlan_id_parsed[1]);
          d_echo('vlan id in db is ' . $vlan_id_parsed[1]);
    //need to determine whats need updating
         if($vlan_cleaned != $vlan_mapping[$port_attr[0]]) {
             echo "update";
	     //Vlan::where('device_id');
             Vlan::where('vlan_id', $vlan_id_parsed[1])->update(['vlan_name' => $vlan_cleaned]);
             log_event("VLAN $vlan_id changed name {$vlan_id_check} -> {$vlan_cleaned} ", $device, 'vlan', 3, $vlan_data['vlan_id']);
         } else {
             echo 'leave alone';
         }
    } else {

    //  Vlan::create( [
    //      'device_id' => $device['device_id'],
    //      'vlan_vlan'      => $port_vlan_map['vlan'],
    //      'vlan_name' => $vlan_mapping[$port_attr[0]],
    //  ]);
        d_echo('create');

    }

    $db_id = $from_db['port_vlan_id'];
    echo "[$db_id]";
//     $db_updater = Vlans::database()->update($db_a, 'ports_vlans', '`port_vlan_id` = ?', [$port_vlan_map['port'], 0, 'unknown', 0, $port_vlan_map['vlan'], $db_id]);
//     d_echo([$db_updater]);
}

foreach ($vlan_mapping as $vlanIdKey => $vlan) {
    $vlan_id = $vlanIdKey;
    $vlan_name = $vlan;
    $vtpdomain_id = '1';
    d_echo('Processing vlan ID: ' . $vlan_id);
    $vlanDB = Vlan::firstOrNew([
        'device_id' => $device['device_id'],
        'vlan_vlan' => $vlan_id,
    ], [
        'vlan_domain' => $vtpdomain_id,
        'vlan_name' => $vlan_name,
    ]);

    if (! $vlanDB->exists) {
        \App\Models\Eventlog::log("Vlan added: $vlan_id with name $vlan_name ", $device['device_id'], 'vlan', 4);
    }

    if ($vlanDB->vlan_name != $vlan_name) {
        $vlanDB->vlan_name = $vlan_name;
        \App\Models\Eventlog::log("Vlan changed: $vlan_id new name $vlan_name", $device['device_id'], 'vlan', 4);
    }

    $vlanDB->save();
    $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;
    $untag = snmpwalk_cache_oid($device, 'wwpLeosVlanTagMemberEntry', [], 'CIENA-WWP-LEOS-VLAN-TAG-MIB');

    foreach ($vlanItemKey as $key => $untag) {
        $key = explode('.', $key);
        echo "\nvlan keys: $key\n";
    }
}
echo PHP_EOL;
