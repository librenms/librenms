<?php

echo 'TNMS-NBI-MIB: ';

/*
 * Get a list of all the known NE for this host.
 */

$db_info_list = dbFetchRows('SELECT id, neID, neType, neName, neLocation, neAlarm, neOpMode, neOpState FROM tnmsneinfo WHERE device_id = ?', array($device['device_id']));
$current_tnmsneinfo = snmpwalk_cache_multi_oid($device, 'enmsNETable', array(), 'TNMS-NBI-MIB');

foreach ($db_info_list as $db_info) {
    $tnmsne_info = array();

    $tnmsne_info['neType']     = $current_tnmsneinfo[$db_info['neID']]['enmsNeType'];
    $tnmsne_info['neName']     = $current_tnmsneinfo[$db_info['neID']]['enmsNeName'];
    $tnmsne_info['neLocation'] = $current_tnmsneinfo[$db_info['neID']]['enmsNeLocation'];
    $tnmsne_info['neAlarm']    = $current_tnmsneinfo[$db_info['neID']]['enmsNeAlarmSeverity'];
    $tnmsne_info['neOpMode']   = $current_tnmsneinfo[$db_info['neID']]['enmsNeOperatingMode'];
    $tnmsne_info['neOpState']  = $current_tnmsneinfo[$db_info['neID']]['enmsNeOpState'];

    foreach ($tnmsne_info as $property => $value) {
        /*
         * Check the property for any modifications.
         */
        if ($tnmsne_info[$property] != $db_info[$property]) {
            dbUpdate(array($property => mres($tnmsne_info[$property])), 'tnmsneinfo', '`id` = ?', array($db_info['id']));
            if ($db_info['neName'] != null) {
                log_event("Coriant NE ".mres($db_info['neName']) . ' (' . $db_info[$property] . ') -> ' . $tnmsne_info[$property], $device);
            }
        }
    }
}

/*
 * Finished discovering NE information.
 */

unset($db_info, $db_info_list, $current_mefinfo);
echo PHP_EOL;
