<?php

$vtpdomains = snmpwalk_cache_oid($device, 'vlanManagementDomains', array(), 'CISCO-VTP-MIB');
$vlans      = snmpwalk_cache_twopart_oid($device, 'vtpVlanEntry', array(), 'CISCO-VTP-MIB');

foreach ($vtpdomains as $vtpdomain_id => $vtpdomain) {
    echo "VTP Domain $vtpdomain_id {$vtpdomain['managementDomainName']}> ";
    foreach ($vlans[$vtpdomain_id] as $vlan_raw => $vlan) {
        echo "$vlan_raw ";
        if (!array_key_exists($vlan_raw, $vlans_dict)) {
            $newvlan_id = dbInsert(array(
                'device_id' => $device['device_id'],
                'vlan_domain' => $vtpdomain_id,
                'vlan_vlan' => $vlan_raw,
                'vlan_name' => $vlan['vtpVlanName'],
                'vlan_type' => $vlan['vtpVlanType']
            ), 'vlans');
            $vlans_dict[$vlan_raw] = $newvlan_id;
        }

        if ($vlan['vtpVlanState'] == 'operational') {
            $device_vlan = array_merge($device, array('community' => $device['community'] . '@' . $vlan_raw, 'context_name' => "vlan-$vlan_raw"));

            $FdbPort_table = snmp_walk($device_vlan, 'dot1dTpFdbPort', '-OqsX', 'BRIDGE-MIB');
            if (empty($FdbPort_table)) {
                // If there are no entries for the vlan, continue
                unset($device_vlan);
                continue;
            }

            $dot1dBasePortIfIndex = snmp_walk($device_vlan, 'dot1dBasePortIfIndex', '-OqsX', 'BRIDGE-MIB');

            foreach (explode("\n", $dot1dBasePortIfIndex) as $dot1dBasePortIfIndex_entry) {
                if (!empty($dot1dBasePortIfIndex_entry)) {
                    preg_match('~dot1dBasePortIfIndex\[(\d+)]\s(\d+)~', $dot1dBasePortIfIndex_entry, $matches);
                    $portid_dict[$matches[1]] = $ifIndex_dict[$matches[2]];
                }
            }

            foreach (explode("\n", $FdbPort_table) as $FdbPort_entry) {
                preg_match('~(?P<oid>\w+)\[(?P<mac>[\w:-]+)]\s(?P<result>\w.*)~', $FdbPort_entry, $matches);
                if (! empty($matches)) {
                    list($oct_1, $oct_2, $oct_3, $oct_4, $oct_5, $oct_6) = explode(':', $matches['mac']);
                    $mac_address = zeropad($oct_1) . zeropad($oct_2) . zeropad($oct_3) . zeropad($oct_4) . zeropad($oct_5) . zeropad($oct_6);
                    if (strlen($mac_address) != 12) {
                        echo 'Mac Address padding failed';
                        continue;
                    } else {
                        $vlan_id = $vlans_dict[$vlan_raw];
                        $dot1dBasePort = $matches['result'];
                        $insert[$vlan_id][$mac_address]['port_id'] = $portid_dict[$dot1dBasePort];
                        d_echo("vlan $vlan_raw - mac $mac_address - port ($port) ".$portid_dict[$dot1dBasePort]."\n");
                    }
                }
            }

            unset($device_vlan);
        } //end if operational
    } // end for each vlan
    echo PHP_EOL;
} // end for each vlan domain
