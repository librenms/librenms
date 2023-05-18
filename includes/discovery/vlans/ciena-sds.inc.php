<?php

use App\Models\Vlan;
echo 'CIENA-WWP-LEOS-VLAN-TAG-MIB VLANs: ';

$vlansName = snmpwalk_cache_oid($device, 'wwpLeosVlanName', array(), 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
$vlansPort = snmpwalk_cache_oid($device, 'wwpLeosVlanMemberTagId', array(), 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
$portsToVlans = snmpwalk_cache_oid($device, 'wwpLeosVlanTagMemberEntry', array(), 'CIENA-WWP-LEOS-VLAN-TAG-MIB');

$vlanMapping = array();
$vlanidToPort = array();

$tmp_tag    = 'wwpLeosVlanMemberTagId';
$tmp_name   = 'wwpLeosVlanName';

// Create New Array with VLAN ID as Key and vlanItem (Vlan Name)
foreach ($vlansName as $vlansKey => $vlansValue){
    foreach ($vlansValue as $vlanItem){
        $vlanMapping["$vlansKey"] = "$vlanItem";
    }
}

// // Dump new VlanId to Vlan Name for debugging.
//need to modify this to create indice for below
echo "Vlan Names:";
foreach ($vlanMapping as $vlanItemKey => $vlanItemValue){
     echo ("VlanID: {$vlanItemKey} Name: {$vlanItemValue}");
}

// Create New Array with Port and VlanID
foreach ($vlansPort as $portIndex => $vlantoport){
    $portIndexArray = explode('.',$portIndex);
    $vlanidToPort["$portIndex"] = "$portIndexArray[2]";
    echo ("\nportindex: {$portIndexArray[2]}\n");
}

// Dump New VlanId to Port Mapping for debugging.
 echo "\nVID to Port Mapping";
 foreach ($vlanidToPort as $vlanPortKey => $vlanPortKeyValue){

     //echo ("Port: {$vlanPortKey} VlanID: {$vlanPortKeyValue}");
     $port_attr = explode(".", $vlanPortKey, 3);
     echo ("\nport number is: {$port_attr[1]}\n");
     echo ("\nVLAN ID is: {$vlanPortKeyValue}\n");
     //echo ("\nport number is: {$port_attr[2]}\n");
     $port_to_dot1d = "1000".$port_attr[1];
     d_echo($port_to_dot1d);
     $port_vlan_map['port'] = (int)$port_to_dot1d;
     //$port_attr[1];
     $port_vlan_map['vlan'] = (int)$port_attr[0];
     d_echo([$port_vlan_map['port']]);
     $db_a['baseport'] = $port_attr[1];
     $db_a['priority'] = "0";
     $db_a['state'] = "unknown";
     $db_a['cost'] = "0";
     $db_a['untagged'] = "0";
     //$port_to_dot1d = "1000".$port_attr[1];
     //d_echo($port_to_dot1d);
     //$port_vlan_map['port'] = (int)$port_to_dot1d;
     //$port_attr[1];
     //$port_vlan_map['vlan'] = (int)$port_attr[0];
     d_echo([$port_vlan_map['port']]);
     d_echo ([$db_a]);
     $from_db_portid_ifIndex =  dbFetchRow('SELECT `port_id` from ports WHERE ports.device_id = ? AND ifIndex = ?', [$device['device_id'], $port_vlan_map['port']]);
     d_echo([$from_db_portid_ifIndex]);
     $db_w = [
         'device_id' => $device['device_id'],
         'port_id'   => $from_db_portid_ifIndex['port_id'] ?? null,
//         'port_id'   => $port_vlan_map['port'] ?? null,
         'vlan'      => $port_vlan_map['vlan'],
     ];
     $db_id = dbInsert(array_merge($db_w, $db_a), 'ports_vlans');
     echo([$db_id]);
     $from_db = dbFetchRow('SELECT * FROM `ports_vlans` WHERE device_id = ? AND port_id = ? AND `vlan` = ?', [$device['device_id'], $port_vlan_map['port'] ?? null, $port_vlan_map['vlan']]);
     $db_id = $from_db['port_vlan_id'];
     d_echo([$from_db]);
     $db_updater = dbUpdate($db_a, 'ports_vlans', '`port_vlan_id` = ?', [$port_vlan_map['port'],0,"unknown",0,$port_vlan_map['vlan'],$db_id]);
     d_echo([$db_updater]);

     //dbUpdate($db_a,'ports_vlans', '`port_vlan_id` = ?',' [$port_attr[1],$vlanPortKeyValue]);
     //$port_vlan_array = array("port","vlan");

     //$port_vlan_array = array("port" => $port_attr[1],"vlan" => $vlanPortKeyValue);
     //$port_vlan_array[0] = $port_attr[1];
     //$port_vlan_array[1] = $vlanPortKeyValue;

     //echo ("\nport vlan array: {$port_vlan_array}\n";

     //dbUpdate('', 'vlans', '`vlan_id` = ?', '[{$port_attr[1]},{$vlanPortKeyValue}]');
     //$tagness_by_vlan_index[$vlanPortKeyValue][$port_attr[1]]['tag'] = 0;
     //echo("\ntaggness maybe: {$tagness_by_vlan_index[$vlanPortKeyValue]["1000".$port_attr[1]]['tag'] = 0;}\n";
}

foreach ($vlanMapping as $vlanIdKey => $vlan) {
    $vlan_id = $vlanIdKey;
    $vlan_name = $vlan;
    $vtpdomain_id = '1';
    d_echo('Processing vlan ID: ' . $vlan_id);

    //try to get existing data from DB
    $vlanDB = Vlan::firstOrNew([
        'device_id' => $device['device_id'],
        'vlan_vlan' => $vlan_id,
    ], [
        'vlan_domain' => $vtpdomain_id,
        'vlan_name' => $vlan_name,
    ]);

    //vlan does not exist
    if (! $vlanDB->exists) {
        \App\Models\Eventlog::log("Vlan added: $vlan_id with name $vlan_name ", $device['device_id'], 'vlan', 4);
    }

    if ($vlanDB->vlan_name != $vlan_name) {
        $vlanDB->vlan_name = $vlan_name;
        \App\Models\Eventlog::log("Vlan changed: $vlan_id new name $vlan_name", $device['device_id'], 'vlan', 4);
    }

    $vlanDB->save();

    $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id; //populate device['vlans'] with ID's

    //ciena map ports and vlans
    $untag = snmpwalk_cache_oid($device, 'wwpLeosVlanTagMemberEntry', array(), 'CIENA-WWP-LEOS-VLAN-TAG-MIB');
    //echo($untag);
    foreach ($vlanItemKey as $key => $untag) {
        $key = explode('.', $key);
        echo("\nvlan keys: $key\n");
    }

}
echo PHP_EOL;
