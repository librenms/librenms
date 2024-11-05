<?php

use App\Models\Vlan;

echo 'ALCATEL-IND1-VLAN-MGR-MIB VLANs: ';

$vtpdomain_id = '1';
$vlans = snmpwalk_cache_oid($device, 'vlanDescription', [], 'ALCATEL-IND1-VLAN-MGR-MIB', 'nokia');

foreach ($vlans as $vlan_id => $vlan) {
    d_echo(" $vlan_id");
    if (is_array($vlans_db[$vtpdomain_id][$vlan_id])) {
        $vlan_data = $vlans_db[$vtpdomain_id][$vlan_id];
        if ($vlan_data['vlan_name'] != $vlan['vlanDescription']) {
            $vlan_upd['vlan_name'] = $vlan['vlanDescription'];
            Vlan::where('vlan_id', $vlan_data['vlan_id'])->update(['vlan_name' => $vlan['vlanDescription']]);
            log_event("VLAN $vlan_id changed name {$vlan_data['vlan_name']} -> {$vlan['vlanDescription']} ", $device, 'vlan', 3, $vlan_data['vlan_id']);
            echo 'U';
        } else {
            echo '.';
        }
    } else {
        Vlan::create([
            'device_id' => $device['device_id'],
            'vlan_domain' => $vtpdomain_id,
            'vlan_vlan' => $vlan_id,
            'vlan_name' => $vlan['vlanDescription'],
            'vlan_type' => null,
        ]);
        echo '+';
    }
    $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;
}

$vlanstype = snmpwalk_group($device, 'vpaType', 'ALCATEL-IND1-VLAN-MGR-MIB', 0, [], 'nokia');

foreach ($vlanstype['vpaType'] ?? [] as $vlan_id => $data) {
    foreach ($data as $portidx => $porttype) {
        $per_vlan_data[$vlan_id][$portidx]['untagged'] = ($porttype == 1 ? 1 : 0);
    }
}
