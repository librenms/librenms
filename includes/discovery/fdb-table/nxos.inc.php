<?php

$vtpdomains = snmpwalk_group($device, 'managementDomainName', 'CISCO-VTP-MIB');
$vlans = snmpwalk_group($device, 'vtpVlanEntry', 'CISCO-VTP-MIB', 2);

$device_hw = dbFetchRow('SELECT * FROM `devices` WHERE `device_id` = ?', [$device['device_id']]);

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

                //If true, the Port is a PVLAN port and the port_id wasn't returned in the right format.
                //Also check if we're dealing with a N3K-C30xxxx, others could be fine
                if ($dot1dBasePort < 55 && ! $port_id && str_contains($device_hw['hardware'], 'N3K-C30')) {
                    d_echo("Found BasePort without portID, Port: $dot1dBasePort");
                    //Interfaces on Nexus use the same format: "Ethernetx/y", build interface string
                    $interface_string = "Ethernet1/$dot1dBasePort";
                    d_echo("Interface-Name: $interface_string, trying to find a match in ports Table");
                    $dev_int = dbFetchCell('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifName` = ?', [$device['device_id'], $interface_string]);
                    if (! $dev_int) {
                        d_echo("Interface lookup failed for BasePort: $dot1dBasePort");
                    } else {
                        $port_id = $dev_int;
                    }
                }

                $vlan_id = isset($vlans_dict[$vlan_raw]) ? $vlans_dict[$vlan_raw] : 0;
                $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
                d_echo("vlan $vlan_id mac $mac_address port ($dot1dBasePort) $port_id\n");
            }

            unset($device_vlan);
        } //end if operational
    } // end for each vlan
    echo PHP_EOL;
} // end for each vlan domain
