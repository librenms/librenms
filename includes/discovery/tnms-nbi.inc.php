<?php

/*
 * Variable to hold the discovered MEF Links.
 */
echo "TNMS-NBI-MIB :".PHP_EOL;

$tnmsnenbi_list = array(); /* NE */

/*
 * Fetch information about Virtual Machines.
 */

$ne_oids = snmpwalk_cache_multi_oid($device, 'enmsNETable', $ne_oids, 'TNMS-NBI-MIB');

echo " Network Equipments : ";
foreach ($ne_oids as $index => $entry) {
    $neType     = $entry['enmsNeType'];
    $neName     = $entry['enmsNeName'];
    $neLocation = $entry['enmsNeLocation'];
    $neAlarm    = $entry['enmsNeAlarmSeverity'];
    $neOpMode   = $entry['enmsNeOperatingMode'];
    $neOpState  = $entry['enmsNeOpState'];
        
    /*
     * Check if the NE is already known for this host
     */
    if (dbFetchCell("SELECT COUNT(id) FROM `tnmsneinfo` WHERE `device_id` = ? AND `neID` = ?", array($device['device_id'], $index)) == 0) {
        $neid = dbInsert(array('device_id' => $device['device_id'], 'neID' => $index, 'neType' => mres($neType), 'neName' => mres($neName), 'neLocation' => mres($neLocation), 'neAlarm' => mres($neAlarm), 'neOpMode' => mres($neOpMode), 'neOpState' => mres($neOpState)), 'tnmsneinfo');
        log_event("Coriant NE Hardware ". mres($neType) . " : " . mres($neName) . " (" . $index . ") at " . mres($neLocation) . " Discovered", $device, 'system', 2);
        echo '+';
    } else {
        echo '.';
    }
    /*
     * Save the discovered MEF Link
     */
    $tnmsnenbi_list[] = $index;
}

$sql = "SELECT id, neID, neName FROM tnmsneinfo WHERE device_id = '".$device['device_id']."'";
foreach (dbFetchRows($sql) as $db_ne) {
    /*
     * Delete the NE HW that are removed from the host.
     */
    if (!in_array($db_ne['neID'], $tnmsnenbi_list)) {
        dbDelete('tnmsneinfo', '`id` = ?', array($db_ne['id']));
        log_event("Coriant NE Hardware ".mres($db_ne['neName']).' at ' . mred($db_ne['neLocation']) . ' Removed', $device, 'system', $db_mef['neID']);
        echo '-';
    }
}

unset($tnmsnenbi_list, $ne_oid, $mo_oids, $sql);
echo PHP_EOL;
