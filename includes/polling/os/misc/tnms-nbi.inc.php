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

function SqlNEAlarmCreate($table, $alarmmib, $device)
{
    $ne_alarm_oids =  snmpwalk_cache_multi_oid($device, $alarmmib, array(), 'TNMS-NBI-MIB');
    $ne_alarm_nums = array_column($ne_alarm_oids,'enmsAlAlarmNumber');
    $max_alarm_nums = max($ne_alarm_nums);
    $sql_alarm_count = dbFetchCell("SELECT max(alarm_num) FROM $table WHERE `device_id` = ? AND `alarm_num` = ?", array($device['device_id'],$max_alarm_nums));

    if ($max_alarm_nums = $sql_alarm_count) {
        echo "Number of alarms correct, nothing to delete or insert \n";
        }
    else {
        foreach($ne_alarm_oids as $alarm_key => $alarm_value){
            $alarm_locations = $alarm_value['enmsAlAffectedLocation'];
            $alarm_num = $alarm_value['enmsAlAlarmNumber'];
            $tnmsne_info_id = $alarm_value['enmsAlNEId'];
            $alarm_cause = $alarm_value['enmsAlProbableCauseString'];
            $timestamp = $alarm_value['enmsAlTimeStamp'];
            $alarm_sev = $alarm_value['enmsAlSeverity'];
            if(is_null($alarm_locations)){
                unset($alarm_value['enmsAlAffectedLocation']);
            }
            else {
                dbinsert(array('device_id' => $device['device_id'],'neID' => mres($neID),'neAlarmtimestamp' => mres($timestamp), 'alarm_cause' => mres($alarm_cause),'alarm_location' => mres($alarm_locations),'alarm_num' => mres($alarm_num), 'alarm_sev' => mres($alarm_sev)), $table);
            }
        }
    }
}
SqlNEAlarmCreate('tnmsalarms', 'enmsAlarmtable', $device);

echo PHP_EOL;
