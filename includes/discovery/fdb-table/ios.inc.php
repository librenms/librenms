<?php

$vtpdomains = snmpwalk_group($device, 'managementDomainName', 'CISCO-VTP-MIB');
$vlans = snmpwalk_group($device, 'vtpVlanEntry', 'CISCO-VTP-MIB', 2);

foreach ($vtpdomains as $vtpdomain_id => $vtpdomain) {
    echo "VTP Domain $vtpdomain_id {$vtpdomain['managementDomainName']}> ";
    foreach ($vlans[$vtpdomain_id] as $vlan_raw => $vlan) {
        echo "$vlan_raw ";
        if (! array_key_exists($vlan_raw, $vlans_dict)) {
            $newvlan_id = dbInsert([
                'device_id' => $device['device_id'],
                'vlan_domain' => $vtpdomain_id,
                'vlan_vlan' => $vlan_raw,
                'vlan_name' => $vlan['vtpVlanName'],
                'vlan_type' => $vlan['vtpVlanType'],
            ], 'vlans');
            $vlans_dict[$vlan_raw] = $newvlan_id;
        }

        if (($vlan['vtpVlanState'] === '1') && ($vlan_raw < 1002 || $vlan_raw > 1005)) {
            $device_vlan = array_merge($device, ['community' => $device['community'] . '@' . $vlan_raw, 'context_name' => "vlan-$vlan_raw"]);

            $fdbPort_table = snmpwalk_group($device_vlan, 'dot1dTpFdbPort', 'BRIDGE-MIB', 0);

            $portid_dict = [];
            $dot1dBasePortIfIndex = snmpwalk_group($device_vlan, 'dot1dBasePortIfIndex', 'BRIDGE-MIB');
            foreach ($dot1dBasePortIfIndex as $portLocal => $data) {
                $port = get_port_by_index_cache($device['device_id'], $data['dot1dBasePortIfIndex']);
                $portid_dict[$portLocal] = $port['port_id'];
            }

            foreach ((array) $fdbPort_table['dot1dTpFdbPort'] as $mac => $dot1dBasePort) {
                $mac_address = implode(array_map('zeropad', explode(':', $mac)));
                if (strlen($mac_address) != 12) {
                    d_echo("MAC address padding failed for $mac\n");
                    continue;
                }
                $port_id = $portid_dict[$dot1dBasePort];
                $vlan_id = isset($vlans_dict[$vlan_raw]) ? $vlans_dict[$vlan_raw] : 0;
                $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
                d_echo("vlan $vlan_id mac $mac_address port ($dot1dBasePort) $port_id\n");
            }

            unset($device_vlan);
        } //end if operational
    } // end for each vlan
    echo PHP_EOL;
} // end for each vlan domain
