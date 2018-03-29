<?php

/*
 * Sensor State discovery module for the CradlePoint WiPipe Platform
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo "CradlePoint WiPipe";

foreach ($pre_cache['wipipe_oids'] as $index => $entry) {
    // Modem Connection Status
    if ($entry['mdmStatus']) {
        $cur_oid = '.1.3.6.1.4.1.20992.1.2.2.1.5.';
        //Create State Index
        $state_name = 'mdmStatus';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'established',0,1,0) ,
                 array($state_index_id,'establishing',0,2,0) ,
                 array($state_index_id,'ready',0,3,0) ,
                 array($state_index_id,'error',0,4,2) ,
                 array($state_index_id,'disconnected',0,5,2) ,
                 array($state_index_id,'disconnecting',0,6,1) ,
                 array($state_index_id,'suspended',0,7,2) ,
                 array($state_index_id,'empty',0,8,3) ,
                 array($state_index_id,'notconfigured',0,9,3) ,
                 array($state_index_id,'userstopped',0,10,1)
             );
            foreach ($states as $value) {
                $insert = array(
                    'state_index_id' => $value[0],
                    'state_descr' => $value[1],
                    'state_draw_graph' => $value[2],
                    'state_value' => $value[3],
                    'state_generic_value' => $value[4]
                );
                dbInsert($insert, 'state_translations');
            }
        }
        // Get Modem Model & Phone Number for description
        $modemdesc = $entry['mdmDescr'];
        $modemmdn = $entry['mdmMDN'];
        $descr = "mdmStatus - " . $modemdesc . " - " . $modemmdn;
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $entry['mdmStatus'], 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
// Device Firmware Upgrade Status
$upgradestatus = snmpwalk_cache_oid($device, 'devFWUpgradeStatus', array(), 'WIPIPE-MIB');
foreach ($upgradestatus as $index => $entry) {
    $cur_oid = '.1.3.6.1.4.1.20992.1.1.4.';
    //Create State Index
    $state_name = 'devFWUpgradeStatus';
    $state_index_id = create_state_index($state_name);
    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'idle',0,1,0) ,
             array($state_index_id,'upgrading',0,2,0) ,
             array($state_index_id,'uptodate',0,3,0) ,
             array($state_index_id,'updateAvail',0,4,1) ,
             array($state_index_id,'failure',0,5,2)
         );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],
                'state_generic_value' => $value[4]
            );
            dbInsert($insert, 'state_translations');
        }
    }
    $descr = "Firmware Upgrade Status";
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $entry['devFWUpgradeStatus'], 'snmp', $index);
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}
