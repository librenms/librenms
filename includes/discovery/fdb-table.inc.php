<?php
$continue = true;

// Build ifIndex to port_id dictionary
$ifIndex_dict = array();
foreach (dbFetchRows("SELECT `ifIndex`,`port_id` FROM `ports` WHERE `device_id` = ?", array($device['device_id'])) as $port_entry) {
    $ifIndex_dict[$port_entry['ifIndex']] = $port_entry['port_id'];
}

// Build dot1dBasePort to port_id dictionary
$portid_dict = array();

// Build a dictionary of vlans in database
$vlans_dict = array();
foreach (dbFetchRows("SELECT `vlan_id`, `vlan_vlan` from `vlans` WHERE `device_id` = ?", array($device['device_id'])) as $vlan_entry) {
    $vlans_dict[$vlan_entry['vlan_vlan']] = $vlan_entry['vlan_id'];
}

// Discover FDB entries
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
                    }
                }
            }

            unset($device_vlan);
        } //end if operational
    }// end vlan for ios
} else {
    echo "OS not yet implemented \n";
    $continue = false;
}

if ($continue) {
    // Build table of existing vlan/mac table
    $existing_fdbs = array();
    $sql_result = dbFetchRows("SELECT * FROM `ports_fdb` WHERE `device_id` = ?", array($device['device_id']));
    foreach ($sql_result as $entry) {
        $existing_fdbs[$entry['vlan_id']][$entry['mac_address']] = $entry;
    }
    // Insert to database
    foreach ($insert as $vlan_id => $mac_address_table) {
        foreach ($mac_address_table as $mac_address_entry => $value) {
            // If existing entry
            if ($existing_fdbs[$vlan_id][$mac_address_entry]) {
                unset($update_entry);

                // Look for columns that need to be updated
                $new_port = $insert[$vlan_id][$mac_address_entry]['port_id'];

                if ($existing_fdbs[$vlan_id][$mac_address_entry]['port_id'] != $new_port) {
                    $update_entry['port_id'] = $new_port;
                }

                if (! empty($update_entry)) {
                    dbUpdate($update_entry, 'ports_fdb', '`device_id` = ? AND `vlan_id` = ? AND `mac_address` = ?', array($device['device_id'], $vlan_id, $mac_address));
                }
                unset($existing_fdbs[$vlan_id][$mac_address_entry]);
            } else {
                $new_entry = array(
                    'port_id' => $value['port_id'],
                    'mac_address' => $mac_address_entry,
                    'vlan_id' => $vlan_id,
                    'device_id' => $device['device_id'],
                );

                dbInsert($new_entry, 'ports_fdb');
            }
        }
    }

    // Delete old entries from the database
    foreach ($existing_fdbs as $vlan_group => $mac_address_table) {
        foreach ($mac_address_table as $mac_address_entry => $value) {
            dbDelete('ports_fdb', '`port_id` = ? AND `mac_address` = ? AND `vlan_id` = ? and `device_id` = ?', array($value['port_id'], $value['mac_address'], $value['vlan_id'], $value['device_id']));
        }
    }
} // end if $continue
