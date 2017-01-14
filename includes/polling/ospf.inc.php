<?php

$ospf_instance_count  = 0;
$ospf_port_count      = 0;
$ospf_area_count      = 0;
$ospf_neighbour_count = 0;

$ospf_oids_db = array(
    'ospfRouterId',
    'ospfAdminStat',
    'ospfVersionNumber',
    'ospfAreaBdrRtrStatus',
    'ospfASBdrRtrStatus',
    'ospfExternLsaCount',
    'ospfExternLsaCksumSum',
    'ospfTOSSupport',
    'ospfOriginateNewLsas',
    'ospfRxNewLsas',
    'ospfExtLsdbLimit',
    'ospfMulticastExtensions',
    'ospfExitOverflowInterval',
    'ospfDemandExtensions',
);

$ospf_area_oids = array(
    'ospfAuthType',
    'ospfImportAsExtern',
    'ospfSpfRuns',
    'ospfAreaBdrRtrCount',
    'ospfAsBdrRtrCount',
    'ospfAreaLsaCount',
    'ospfAreaLsaCksumSum',
    'ospfAreaSummary',
    'ospfAreaStatus',
);

$ospf_port_oids = array(
    'ospfIfIpAddress',
    'port_id',
    'ospfAddressLessIf',
    'ospfIfAreaId',
    'ospfIfType',
    'ospfIfAdminStat',
    'ospfIfRtrPriority',
    'ospfIfTransitDelay',
    'ospfIfRetransInterval',
    'ospfIfHelloInterval',
    'ospfIfRtrDeadInterval',
    'ospfIfPollInterval',
    'ospfIfState',
    'ospfIfDesignatedRouter',
    'ospfIfBackupDesignatedRouter',
    'ospfIfEvents',
    'ospfIfAuthKey',
    'ospfIfStatus',
    'ospfIfMulticastForwarding',
    'ospfIfDemand',
    'ospfIfAuthType',
);

$ospf_nbr_oids_db  = array(
    'ospfNbrIpAddr',
    'ospfNbrAddressLessIndex',
    'ospfNbrRtrId',
    'ospfNbrOptions',
    'ospfNbrPriority',
    'ospfNbrState',
    'ospfNbrEvents',
    'ospfNbrLsRetransQLen',
    'ospfNbmaNbrStatus',
    'ospfNbmaNbrPermanence',
    'ospfNbrHelloSuppressed',
);

$ospf_nbr_oids_rrd = array();
$ospf_nbr_oids     = array_merge($ospf_nbr_oids_db, $ospf_nbr_oids_rrd);

if (key_exists('vrf_lite_cisco', $device) && ($device['vrf_lite_cisco'] != '')) {
    $vrfs_lite_cisco = $device['vrf_lite_cisco'];
} else {
    $vrfs_lite_cisco = array(array('context_name' => null));
}

foreach ($vrfs_lite_cisco as $vrf_lite) {
    $device['context_name'] = $vrf_lite['context_name'];

    echo 'Processes: ';
    
    // Build array of existing entries
    foreach (dbFetchRows('SELECT * FROM `ospf_instances` WHERE `device_id` = ? AND `context_name` = ?', array($device['device_id'], $device['context_name'])) as $entry) {
        $ospf_instances_db[$entry['ospf_instance_id']][$entry['context_name']] = $entry;
    }

    // Pull data from device
    $ospf_instances_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfGeneralGroup', array(), 'OSPF-MIB');
    foreach ($ospf_instances_poll as $ospf_instance_id => $ospf_entry) {
        // If the entry doesn't already exist in the prebuilt array, insert into the database and put into the array
        if (empty($ospf_instances_db[$ospf_instance_id][$device['context_name']])) {
            dbInsert(array('device_id' => $device['device_id'], 'ospf_instance_id' => $ospf_instance_id, 'context_name' => $device['context_name']), 'ospf_instances');
            echo '+';
            $entry = dbFetchRow('SELECT * FROM `ospf_instances` WHERE `device_id` = ? AND `ospf_instance_id` = ? AND `context_name` = ?', array($device['device_id'], $ospf_instance_id, $device['context_name']));
            $ospf_instances_db[$ospf_instance_id][$device['context_name']] = $entry;
        }
    }

    if ($debug) {
        echo "\nPolled: ";
        print_r($ospf_instances_poll);
        echo 'Database: ';
        print_r($ospf_instances_db);
        echo "\n";
    }

    // Loop array of entries and update
    if (is_array($ospf_instances_db)) {
        foreach ($ospf_instances_db as $ospf_instance_id => $ospf_instance_db) {
            if (is_array($ospf_instances_poll[$ospf_instance_id])) {
                $ospf_instance_poll = $ospf_instances_poll[$ospf_instance_id];
                foreach ($ospf_oids_db as $oid) {
                    // Loop the OIDs
                    if ($ospf_instance_db[$device['context_name']][$oid] != $ospf_instance_poll[$oid]) {
                        // If data has changed, build a query
                        $ospf_instance_update[$oid] = set_numeric($ospf_instance_poll[$oid]);
                        // log_event("$oid -> ".$this_port[$oid], $device, 'ospf', $port['port_id']); // FIXME
                    }
                }

                if ($ospf_instance_update) {
                    dbUpdate($ospf_instance_update, 'ospf_instances', '`device_id` = ? AND `ospf_instance_id` = ? AND `context_name`=? ', array($device['device_id'], $ospf_instance_id, $device['context_name']));
                    echo 'U';
                    unset($ospf_instance_update);
                } else {
                    echo '.';
                }

                unset($ospf_instance_poll);
                unset($ospf_instance_db);
                $ospf_instance_count++;
            } else {
                dbDelete('ospf_instances', '`device_id` = ? AND `ospf_instance_id` = ? AND `context_name`=? ', array($device['device_id'], $ospf_area_db['ospfAreaId'], $device['context_name']));
            }
        }//end foreach
    }//end if
    unset($ospf_instances_poll);
    unset($ospf_instances_db);

    echo ' Areas: ';

    // Build array of existing entries
    foreach (dbFetchRows('SELECT * FROM `ospf_areas` WHERE `device_id` = ? AND `context_name`= ?', array($device['device_id'], $device['context_name'])) as $entry) {
        $ospf_areas_db[$entry['ospfAreaId']][$entry['context_name']] = $entry;
    }

    // Pull data from device
    $ospf_areas_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfAreaEntry', array(), 'OSPF-MIB');

    foreach ($ospf_areas_poll as $ospf_area_id => $ospf_area) {
        // If the entry doesn't already exist in the prebuilt array, insert into the database and put into the array
        if (empty($ospf_areas_db[$ospf_area_id][$device['context_name']])) {
            dbInsert(array('device_id' => $device['device_id'], 'ospfAreaId' => $ospf_area_id, 'context_name' => $device['context_name']), 'ospf_areas');
            echo '+';
            $entry = dbFetchRows('SELECT * FROM `ospf_areas` WHERE `device_id` = ? AND `ospfAreaId` = ? AND `context_name` = ?', array($device['device_id'], $ospf_area_id, $device['context_name']));
            $ospf_areas_db[$ospf_area_id][$device['context_name']] = $entry;
        }
    }

    if ($debug) {
        echo "\nPolled: ";
        print_r($ospf_areas_poll);
        echo 'Database: ';
        print_r($ospf_areas_db);
        echo "\n";
    }

    // Loop array of entries and update
    if (is_array($ospf_areas_db)) {
        foreach ($ospf_areas_db as $ospf_area_id => $ospf_area_db) {
            if (is_array($ospf_areas_poll[$ospf_area_id])) {
                $ospf_area_poll = $ospf_areas_poll[$ospf_area_id];
                foreach ($ospf_area_oids as $oid) {
                    // Loop the OIDs
                    if ($ospf_area_db[$device['context_name']][$oid] != $ospf_area_poll[$oid]) {
                        // If data has changed, build a query
                        $ospf_area_update[$oid] = $ospf_area_poll[$oid];
                        // log_event("$oid -> ".$this_port[$oid], $device, 'interface', $port['port_id']); // FIXME
                    }
                }

                if ($ospf_area_update) {
                    dbUpdate($ospf_area_update, 'ospf_areas', '`device_id` = ? AND `ospfAreaId` = ? AND `context_name` = ?', array($device['device_id'], $ospf_area_id, $device['context_name']));
                    echo 'U';
                    unset($ospf_area_update);
                } else {
                    echo '.';
                }

                unset($ospf_area_poll);
                unset($ospf_area_db);
                $ospf_area_count++;
            } else {
                dbDelete('ospf_ports', '`device_id` = ? AND `ospfIfAreaId` = ? AND `context_name` = ?', array($device['device_id'], $ospf_area_db['ospfAreaId'], $device['context_name']));
            }//end if
        }//end foreach
    }//end if

    unset($ospf_areas_db);
    unset($ospf_areas_poll);


// $ospf_ports = snmpwalk_cache_oid($device, "OSPF-MIB::ospfIfEntry", array(), "OSPF-MIB");
// print_r($ospf_ports);
    echo ' Ports: ';

    // Build array of existing entries
    foreach (dbFetchRows('SELECT * FROM `ospf_ports` WHERE `device_id` = ? AND `context_name` = ?', array($device['device_id'], $device['context_name'])) as $entry) {
        $ospf_ports_db[$entry['ospf_port_id']][$device['context_name']] = $entry;
    }

    // Pull data from device
    $ospf_ports_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfIfEntry', array(), 'OSPF-MIB');

    foreach ($ospf_ports_poll as $ospf_port_id => $ospf_port) {
        // If the entry doesn't already exist in the prebuilt array, insert into the database and put into the array
        if (empty($ospf_ports_db[$ospf_port_id][$device['context_name']])) {
            dbInsert(array('device_id' => $device['device_id'], 'ospf_port_id' => $ospf_port_id, 'context_name' => $device['context_name']), 'ospf_ports');
            echo '+';
            $ospf_ports_db[$ospf_port_id][$device['context_name']] = dbFetchRow('SELECT * FROM `ospf_ports` WHERE `device_id` = ? AND `ospf_port_id` = ? AND `context_name` = ?', array($device['device_id'], $ospf_port_id, $device['context_name']));
        }
    }

    if ($debug) {
        echo "\nPolled: ";
        print_r($ospf_ports_poll);
        echo 'Database: ';
        print_r($ospf_ports_db);
        echo "\n";
    }

    // Loop array of entries and update
    if (is_array($ospf_ports_db)) {
        foreach ($ospf_ports_db as $ospf_port_id => $ospf_port_db) {
            if (is_array($ospf_ports_poll[$ospf_port_id])) {
                $ospf_port_poll = $ospf_ports_poll[$ospf_port_id];

                if ($ospf_port_poll['ospfAddressLessIf']) {
                    $ospf_port_poll['port_id'] = @dbFetchCell('SELECT `port_id` FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', array($device['device_id'], $ospf_port_poll['ospfAddressLessIf']));
                } else {
                    $ospf_port_poll['port_id'] = @dbFetchCell('SELECT A.`port_id` FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND I.device_id = ? AND A.context_name = ?', array($ospf_port_poll['ospfIfIpAddress'], $device['device_id'], $device['context_name']));
                }

                foreach ($ospf_port_oids as $oid) {
                    // Loop the OIDs
                    if ($ospf_port_db[$device['context_name']][$oid] != $ospf_port_poll[$oid]) {
                        // If data has changed, build a query
                        $ospf_port_update[$oid] = set_numeric($ospf_port_poll[$oid]);
                        // log_event("$oid -> ".$this_port[$oid], $device, 'ospf', $port['port_id']); // FIXME
                    }
                }

                if ($ospf_port_update) {
                    dbUpdate($ospf_port_update, 'ospf_ports', '`device_id` = ? AND `ospf_port_id` = ? AND `context_name` = ?', array($device['device_id'], $ospf_port_id, $device['context_name']));
                    echo 'U';
                    unset($ospf_port_update);
                } else {
                    echo '.';
                }

                unset($ospf_port_poll);
                unset($ospf_port_db);
                $ospf_port_count++;
            } else {
                dbDelete('ospf_ports', '`device_id` = ? AND `ospf_port_id` = ? AND `context_name` = ?', array($device['device_id'], $ospf_port_db['ospf_port_id'], $device['context_name']));
                // ("DELETE FROM `ospf_ports` WHERE `device_id` = '".$device['device_id']."' AND `ospf_port_id` = '".$ospf_port_db['ospf_port_id']."'");
                echo '-';
            }//end if
        }//end foreach
    }//end if


// OSPF-MIB::ospfNbrIpAddr.172.22.203.98.0 172.22.203.98
// OSPF-MIB::ospfNbrAddressLessIndex.172.22.203.98.0 0
// OSPF-MIB::ospfNbrRtrId.172.22.203.98.0 172.22.203.128
// OSPF-MIB::ospfNbrOptions.172.22.203.98.0 2
// OSPF-MIB::ospfNbrPriority.172.22.203.98.0 0
// OSPF-MIB::ospfNbrState.172.22.203.98.0 full
// OSPF-MIB::ospfNbrEvents.172.22.203.98.0 6
// OSPF-MIB::ospfNbrLsRetransQLen.172.22.203.98.0 1
// OSPF-MIB::ospfNbmaNbrStatus.172.22.203.98.0 active
// OSPF-MIB::ospfNbmaNbrPermanence.172.22.203.98.0 dynamic
// OSPF-MIB::ospfNbrHelloSuppressed.172.22.203.98.0 false
    echo ' Neighbours: ';

    

    // Build array of existing entries
    foreach (dbFetchRows('SELECT * FROM `ospf_nbrs` WHERE `device_id` = ? AND `context_name` = ?', array($device['device_id'], $device['context_name'])) as $nbr_entry) {
        $ospf_nbrs_db[$nbr_entry['ospf_nbr_id']][$device['context_name']] = $nbr_entry;
    }

    // Pull data from device
    $ospf_nbrs_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfNbrEntry', array(), 'OSPF-MIB');

    foreach ($ospf_nbrs_poll as $ospf_nbr_id => $ospf_nbr) {
        // If the entry doesn't already exist in the prebuilt array, insert into the database and put into the array
        if (!isset($ospf_nbrs_db[$ospf_nbr_id][$device['context_name']])) {
            dbInsert(array('device_id' => $device['device_id'], 'ospf_nbr_id' => $ospf_nbr_id, 'context_name' => $device['context_name']), 'ospf_nbrs');
            echo '+';
            $entry = dbFetchRow('SELECT * FROM `ospf_nbrs` WHERE `device_id` = ? AND `ospf_nbr_id` = ? AND `context_name` = ?', array($device['device_id'], $ospf_nbr_id,$device['context_name']));
            $ospf_nbrs_db[$ospf_nbr_id][$device['context_name']] = $entry;
        }
    }

    if ($debug) {
        echo "\nPolled: ";
        print_r($ospf_nbrs_poll);
        echo 'Database: ';
        print_r($ospf_nbrs_db);
        echo "\n";
    }

    // Loop array of entries and update
    if (is_array($ospf_nbrs_db)) {
        foreach ($ospf_nbrs_db as $ospf_nbr_id => $ospf_nbr_db) {
            if (is_array($ospf_nbrs_poll[$ospf_nbr_id])) {
                $ospf_nbr_poll = $ospf_nbrs_poll[$ospf_nbr_id];

                $ospf_nbr_poll['port_id'] = @dbFetchCell('SELECT A.`port_id` FROM ipv4_addresses AS A, ospf_nbrs AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND I.device_id = ? AND A.context_name = ?', array($ospf_nbr_poll['ospfNbrIpAddr'], $device['device_id'], $device['context_name']));

                if ($ospf_nbr_db[$device['context_name']]['port_id'] != $ospf_nbr_poll['port_id']) {
                    if (!empty($ospf_nbr_poll['port_id'])) {
                        $ospf_nbr_update = array('port_id' => $ospf_nbr_poll['port_id']);
                    } else {
                        $ospf_nbr_update = array('port_id' => array('NULL'));
                    }
                }

                foreach ($ospf_nbr_oids as $oid) {
                    // Loop the OIDs
                    d_echo($ospf_nbr_db[$device['context_name']][$oid].'|'.$ospf_nbr_poll[$oid]."\n");

                    if ($ospf_nbr_db[$device['context_name']][$oid] != $ospf_nbr_poll[$oid]) {
                        // If data has changed, build a query
                        $ospf_nbr_update[$oid] = set_numeric($ospf_nbr_poll[$oid]);
                        // log_event("$oid -> ".$this_nbr[$oid], $device, 'ospf', $nbr['port_id']); // FIXME
                    }
                }

                if ($ospf_nbr_update) {
                    dbUpdate($ospf_nbr_update, 'ospf_nbrs', '`device_id` = ? AND `ospf_nbr_id` = ? AND `context_name` = ?', array($device['device_id'], $ospf_nbr_id, $device['context_name']));
                    echo 'U';
                    unset($ospf_nbr_update);
                } else {
                    echo '.';
                }

                unset($ospf_nbr_poll);
                unset($ospf_nbr_db);
                $ospf_nbr_count++;
            } else {
                dbDelete('ospf_nbrs', '`device_id` = ? AND `ospf_nbr_id` = ? AND `context_name` = ?', array($device['device_id'], $ospf_nbr_db['ospf_nbr_id'], $device['context_name']));
                echo '-';
            }//end if
        }//end foreach
    }//end if
    unset($ospf_nbrs_db);
    unset($ospf_nbrs_poll);
    echo "\n";
}
unset($device['context_name']);
unset($vrfs_lite_cisco);
// Create device-wide statistics RRD
$rrd_def = array(
    'DS:instances:GAUGE:600:0:1000000',
    'DS:areas:GAUGE:600:0:1000000',
    'DS:ports:GAUGE:600:0:1000000',
    'DS:neighbours:GAUGE:600:0:1000000'
);

$fields = array(
    'instances'   => $ospf_instance_count,
    'areas'       => $ospf_area_count,
    'ports'       => $ospf_port_count,
    'neighbours'  => $ospf_neighbour_count,
);

$tags = compact('rrd_def');
data_update($device, 'ospf-statistics', $tags, $fields);

echo "\n";
