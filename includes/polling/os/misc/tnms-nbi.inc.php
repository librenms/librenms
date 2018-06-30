<?php

echo 'TNMS-NBI-MIB: ';

/*
 * Coriant have done some SQL over SNMP, since we have to populate and update all the tables
 * before using it, we have to do ugly stuff
 */
function SqlSNMP($table, $cmib, $device)
{

    echo " $cmib ";
    $c_oids = snmpwalk_cache_multi_oid($device, $cmib, array(), 'TNMS-NBI-MIB');
    $c_list = array();

    foreach ($c_oids as $index => $entry) {
        $neType     = $entry['enmsNeType'];
        $neName     = $entry['enmsNeName'];
        $neLocation = $entry['enmsNeLocation'];
        $neAlarm    = $entry['enmsNeAlarmSeverity'];
        $neOpMode   = $entry['enmsNeOperatingMode'];
        $neOpState  = $entry['enmsNeOpState'];
        
        if (dbFetchCell("SELECT COUNT(id) FROM $table WHERE `device_id` = ? AND `neID` = ?", array($device['device_id'], $index)) == 0) {
            dbInsert(array('device_id' => $device['device_id'], 'neID' => $index, 'neType' => mres($neType), 'neName' => mres($neName), 'neLocation' => mres($neLocation), 'neAlarm' => mres($neAlarm), 'neOpMode' => mres($neOpMode), 'neOpState' => mres($neOpState)), $table);
            log_event("Coriant $cmib Hardware ". mres($neType) . " : " . mres($neName) . " ($index) at " . mres($neLocation) . " Discovered", $device, 'system', 2);
            echo '+';
        } else {
            echo '.';
        }
        $c_list[] = $index;
    }

    $sql = "SELECT id, neID, neName FROM $table WHERE device_id = ?";
    $params = array($device['device_id']);
    foreach (dbFetchRows($sql, $params) as $db_ne) {
        d_echo($db_ne);
        if (!in_array($db_ne['neID'], $c_list)) {
            dbDelete($table, '`id` = ?', array($db_ne['id']));
            log_event("Coriant $cmib Hardware ".mres($db_ne['neName']).' at ' . mres($db_ne['neLocation']) . ' Removed', $device, 'system', $db_ne['neID']);
            echo '-';
        }
    }
}

SqlSNMP('tnmsneinfo', 'enmsNETable', $device);

echo PHP_EOL;
