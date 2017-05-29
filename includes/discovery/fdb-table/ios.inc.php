<?php

if ($device['os'] == 'ios') {
    echo 'FDB table : ';
    echo("\n");

    $vlans = snmpwalk_cache_oid($device, 'vtpVlanState', array(), 'CISCO-VTP-MIB');
    foreach ($vlans as $vlan_oid => $state) {
        if ($state['vtpVlanState'] == 'operational') {
            $vlan = explode('.', $vlan_oid);
            $vlan = $vlan[1];

            $device_vlan = $device;
            $device_vlan['fdb_vlan'] = $vlan;
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
                        $vlan_id = $vlans_dict[$vlan];
                        $dot1dBasePort = $matches['result'];
                        $insert[$vlan_id][$mac_address]['port_id'] = $portid_dict[$dot1dBasePort];
                        echo "vlan $vlan - mac $mac_address - port ($port) ".$portid_dict[$dot1dBasePort]."\n";
                    }
                }
            }

            unset($device_vlan);
        } //end if operational
    }// end vlan for ios
}
