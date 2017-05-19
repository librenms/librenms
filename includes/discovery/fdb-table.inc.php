<?php
$continue = true;

// Build ifIndex to port_id dictionary
$ifIndex_dict = array();
foreach (dbFetchRows("SELECT `ifIndex`,`port_id` FROM `ports` WHERE `device_id` = ?", array($device['device_id'])) as $port_entry) {
    $ifIndex_dict[$port_entry['ifIndex']] = $port_entry['port_id'];
}
#print_r($ifIndex_dict);

// Build dot1dBasePort to port_id dictionary
$portid_dict = array();

// Discover FDB entries
if ($device['os'] == 'ios') {
    echo 'FDB table : ';
    echo("\n");

    $vlans = snmpwalk_cache_oid($device, 'vtpVlanState', array(), 'CISCO-VTP-MIB');
    foreach ($vlans as $vlan_oid => $state) {
        if ($state['vtpVlanState'] == 'operational') {
            $vlan = explode('.', $vlan_oid);
            echo "VLAN : ".$vlan[1] . "\n";

            $device_vlan = $device;
            $device_vlan['fdb_vlan'] = $vlan[1];
            $device_vlan['snmp_retries]'] = 0;
            $FdbPort_table = snmp_walk($device_vlan, 'dot1dTpFdbPort', '-OqsX', 'BRIDGE-MIB');
            if (empty($FdbPort_table)) {
		echo "No entries...\n";
                // If there are no entries for the vlan, continue
                unset($device_vlan);
                continue;
            }  
            #echo "table:";
	    #print_r($FdbPort_table); 

            $dot1dBasePortIfIndex = snmp_walk($device_vlan, 'dot1dBasePortIfIndex', '-OqsX', 'BRIDGE-MIB');

            foreach (explode("\n", $dot1dBasePortIfIndex) as $dot1dBasePortIfIndex_entry) {
		#echo "port ".$dot1dBasePortIfIndex_entry."\n";
                if (!empty($dot1dBasePortIfIndex_entry)) {
		    $port = explode(' ', $dot1dBasePortIfIndex_entry);
		    $strTemp = explode('[', $port[0]);
		    $portLocal = rtrim($strTemp[1],']');
                    $portid_dict[$portLocal] = $ifIndex_dict[$port[1]];
                }
            }

            foreach (explode("\n", $FdbPort_table) as $FdbPort_entry) {
		#echo $FdbPort_entry."\n";
		$port = explode(' ', $FdbPort_entry);
		$macTemp = explode('[', $port[0]);
		$mac = rtrim($macTemp[1],']');
                if (! empty($mac)) {
                    list($oct_1, $oct_2, $oct_3, $oct_4, $oct_5, $oct_6) = explode(':', $mac);
                    $mac_address = zeropad($oct_1) . zeropad($oct_2) . zeropad($oct_3) . zeropad($oct_4) . zeropad($oct_5) . zeropad($oct_6);
                    if (strlen($mac_address) != 12) {
                        echo 'Mac Address padding failed';
                        continue;
                    } else {
                        $dot1dBasePort = $port[1];
                        $insert[$vlan[1]][$mac_address]['port_id'] = $portid_dict[$dot1dBasePort];
			#echo "vlan $vlan[1] - mac $mac_address - port ".$portid_dict[$dot1dBasePort]."\n";
                    }
                }
            }

            unset($device_vlan);
        } //end if operational
    }// end vlan for ios
    var_dump($insert); exit;
} elseif ($device['os'] == 'timos') {
    echo 'FDB table : ';
    echo("\n");

    $portids = snmp_walk($device, 'tlsFdbPortId', '-OqsX', 'TIMETRA-SERV-MIB');
    $mac_to_port = array();
    foreach (explode("\n", $portids) as $portid) {
        preg_match('~(?P<oid>\w+)\[\d+]\[(?P<mac>[\w:-]+)]\s(?P<result>\d+)~', $portid, $matches);
        if (! empty($matches)) {
            $mac_to_port[$matches['mac']] = $matches['result'];
        }
    }

    $vlans = snmp_walk($device, 'tlsFdbEncapValue', '-OqsX', 'TIMETRA-SERV-MIB');
    foreach (explode("\n", $vlans) as $vlan) {
        preg_match('~(?P<oid>\w+)\[\d+]\[(?P<mac>[\w:-]+)]\s(?P<result>\d+)~', $vlan, $matches);
        if (! empty($matches)) {
            list($oct_1, $oct_2, $oct_3, $oct_4, $oct_5, $oct_6) = explode(':', $matches['mac']);
            $mac_address = zeropad($oct_1) . zeropad($oct_2) . zeropad($oct_3) . zeropad($oct_4) . zeropad($oct_5) . zeropad($oct_6);
            if (strlen($mac_address) != 12) {
                echo 'Mac Address padding failed';
                continue;
            } else {
                $vlan = $matches['result'];
                $ifIndex = $mac_to_port[$matches['mac']];
                $insert[$vlan][$mac_address]['port_id'] = $ifIndex_dict[$ifIndex];
            }
        }
    } //end vlan loop for timos
} elseif ($device['os'] == 'comware') {
    echo 'FDB table : ';
    echo("\n");

    // find fdb entries, output like
    // dot1qTpFdbPort[507][0:24:c4:fd:a1:c7] 1
    $FdbPort_table = snmp_walk($device, 'dot1qTpFdbEntry', '-Cc -OqsX', 'Q-BRIDGE-MIB');

    // find port ids, output like
    // dot1dBasePortIfIndex[1] 1
    $dot1dBasePortIfIndex = snmp_walk($device, 'dot1dBasePortIfIndex', '-Cc -OqsX', 'BRIDGE-MIB'); 

            foreach (explode("\n", $dot1dBasePortIfIndex) as $dot1dBasePortIfIndex_entry) {
                if (!empty($dot1dBasePortIfIndex_entry)) {
                    $port = explode(' ', $dot1dBasePortIfIndex_entry);
                    $strTemp = explode('[', $port[0]);
                    $portLocal = rtrim($strTemp[1],']');
                    $portid_dict[$portLocal] = $ifIndex_dict[$port[1]];
                }
            }

            foreach (explode("\n", $FdbPort_table) as $FdbPort_entry) {
              preg_match('~(?P<oid>\w+)\[(?P<vlan>\d+)]\[(?P<mac>[\w:-]+)]\s(?P<port>\d+)~', $FdbPort_entry, $matches);
              if (! empty($matches)) {
                $port = $matches['port'];
                $mac = $matches['mac'];
                $vlan = $matches['vlan'];
		//echo "vlan $vlan, port $port, mac $mac\n";
                if (! empty($mac)) {
                    list($oct_1, $oct_2, $oct_3, $oct_4, $oct_5, $oct_6) = explode(':', $mac);
                    $mac_address = zeropad($oct_1) . zeropad($oct_2) . zeropad($oct_3) . zeropad($oct_4) . zeropad($oct_5) . zeropad($oct_6);
                    if (strlen($mac_address) != 12) {
                        echo 'Mac Address padding failed';
                        continue;
                    } else {
		        $dot1dBasePort = $port;
                        $insert[$vlan][$mac_address]['port_id'] = $portid_dict[$dot1dBasePort];
                        echo "vlan $vlan - mac $mac_address - port ($port) ".$portid_dict[$dot1dBasePort]."\n";
                    }
                } // end if on empty mac
              } // end if on matches
            } // end loop on FdbPort_entry
} else {
    echo "OS not yet implemented \n";
    $continue = false;
}

#var_dump($insert); exit;

if ($continue) {
    echo "Number of FDB entries: ".count($insert)."\n";
    // Build table of existing vlan/mac table
    $existing_fdbs = array();
    $sql_result = dbFetchRows("SELECT * FROM `ports_fdb` WHERE `device_id` = ?", array($device['device_id']));
    foreach ($sql_result as $entry) {
        $existing_fdbs[$entry['vlan_id']][$entry['mac_address']] = $entry;
    }

    // Insert to database
    echo "INSERT:\n";
    foreach ($insert as $vlan => $mac_address_table) {
	echo "vlan: $vlan\n";
        foreach ($mac_address_table as $mac_address_entry => $value) {
            // If existing entry
            if ($existing_fdbs[$vlan][$mac_address_entry]) {
                unset($update_entry);

                // Look for columns that need to be updated
                $new_port = $insert[$vlan][$mac_address_entry]['port_id'];

                if ($existing_fdbs[$vlan][$mac_address_entry]['port_id'] != $new_port) {
                    $update_entry['port_id'] = $new_port;
                }

                if (! empty($update_entry)) {
                    dbUpdate($update_entry, 'ports_fdb', '`device_id` = ? AND `vlan_id` = ? AND `mac_address` = ?', array($device['device_id'], $vlan, $mac_address));
	            echo "update mac $mac_address\n";
                }
                unset($existing_fdbs[$vlan][$mac_address_entry]);
            } else {
                $new_entry = array();
                $new_entry['port_id'] = $value['port_id'];
                $new_entry['mac_address'] = $mac_address_entry;
                $new_entry['vlan_id'] = $vlan;
                $new_entry['device_id'] = $device['device_id'];

                dbInsert($new_entry, 'ports_fdb');
	        echo "insert mac $mac_address_entry\n";
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
