<?php

use App\Models\Vlan;

echo 'CIENA-WWP-LEOS-VLAN-TAG-MIB VLANs: ';

$vlans_name = snmpwalk_cache_oid($device, 'wwpLeosVlanName', array(), 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
$vlans_port = snmpwalk_cache_oid($device, 'wwpLeosVlanMemberTagId', array(), 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
$ports_to_vlans = snmpwalk_cache_oid($device, 'wwpLeosVlanTagMemberEntry', array(), 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
$vlan_mapping = array();
$vlanid_to_port = array();
$tmp_tag    = 'wwpLeosVlanMemberTagId';
$tmp_name   = 'wwpLeosVlanName';

foreach ($vlans_name as $vlans_key => $vlans_value){
    foreach ($vlans_value as $vlan_item){
        $vlan_mapping["$vlans_key"] = "$vlan_item";
    }
}

foreach ($vlans_port as $port_index => $vlantoport){
    $portindex_array = explode('.',$port_index);
    $vlanid_to_port["$port_index"] = "$portindex_array[2]";
    echo ("\nportindex: {$portindex_array[2]}\n");
}

 echo "\nVID to Port Mapping";
 foreach ($vlanid_to_port as $vlan_port_key => $vlan_port_key_value){
     $port_attr = explode(".", $vlan_port_key, 3);
     echo ("\nport number is: {$port_attr[1]}\n");
     echo ("\nVLAN ID is: {$vlan_port_key_value}\n");
     $port_to_dot1d = "1000".$port_attr[1];
     $port_vlan_map['port'] = (int)$port_to_dot1d;
     $port_vlan_map['vlan'] = (int)$port_attr[0];
     d_echo([$port_vlan_map['port']]);
     $db_a['baseport'] = $port_attr[1];
     $db_a['priority'] = "0";
     $db_a['state'] = "unknown";
     $db_a['cost'] = "0";
     $db_a['untagged'] = "0";
     $from_db_portid_ifIndex =  Vlans::database()->query('SELECT `port_id` from ports WHERE ports.device_id = ? AND ifIndex = ?', [$device['device_id'], $port_vlan_map['port']]);
     $db_w = [
         'device_id' => $device['device_id'],
         'port_id'   => $from_db_portid_ifIndex['port_id'] ?? null,
         'vlan'      => $port_vlan_map['vlan'],
     ];
     $db_id = Vlans::database()->insert(array_merge($db_w, $db_a), 'ports_vlans');
     echo([$db_id]);
     $from_db = Vlans::database()->query('SELECT * FROM `ports_vlans` WHERE device_id = ? AND port_id = ? AND `vlan` = ?', [$device['device_id'], $port_vlan_map['port'] ?? null, $port_vlan_map['vlan']]);
     $db_id = $from_db['port_vlan_id'];
     d_echo([$from_db]);
     $db_updater = Vlans::database()->update($db_a, 'ports_vlans', '`port_vlan_id` = ?', [$port_vlan_map['port'],0,"unknown",0,$port_vlan_map['vlan'],$db_id]);
     d_echo([$db_updater]);
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
    $untag = snmpwalk_cache_oid($device, 'wwpLeosVlanTagMemberEntry', array(), 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
    
    foreach ($vlanItemKey as $key => $untag) {
        $key = explode('.', $key);
        echo("\nvlan keys: $key\n");
    }
}
echo PHP_EOL;
