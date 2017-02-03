<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$tables = array(
    // One could add more entrys from deviceGroup, but this will do as a start
    array('processorDeviceStatusTable','.1.3.6.1.4.1.674.10892.1.1100.32.1.5.','processorDeviceStatusStatus','processorDeviceStatusLocationName','MIB-Dell-10892') ,
    array('memoryDeviceTable','.1.3.6.1.4.1.674.10892.1.1100.50.1.5.','memoryDeviceStatus','memoryDeviceLocationName','MIB-Dell-10892') ,
    array('powerSupplyTable','.1.3.6.1.4.1.674.10892.1.600.12.1.5.','powerSupplyStatus','powerSupplyLocationName','MIB-Dell-10892') ,
    array('intrusionTable','.1.3.6.1.4.1.674.10892.1.300.70.1.5.','intrusionStatus','Intrusion','MIB-Dell-10892') ,
    array('controllerTable','.1.3.6.1.4.1.674.10893.1.20.130.1.1.5.','controllerState','controllerName','StorageManagement-MIB') ,
    array('arrayDiskTable','.1.3.6.1.4.1.674.10893.1.20.130.4.1.4.','arrayDiskState','arrayDiskName','StorageManagement-MIB') ,
    array('virtualDiskTable','.1.3.6.1.4.1.674.10893.1.20.140.1.1.4.','virtualDiskState','virtualDiskDeviceName','StorageManagement-MIB') ,
    array('batteryTable','.1.3.6.1.4.1.674.10893.1.20.130.15.1.4.','batteryState','batteryName','StorageManagement-MIB') ,
);

foreach ($tables as $tablevalue) {
    $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], array(), $tablevalue[4]);
    $cur_oid = $tablevalue[1];

    if (is_array($temp)) {
        //Create State Index
        $state_name = $tablevalue[2];
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            if ($state_name == 'processorDeviceStatusStatus' || $state_name == 'memoryDeviceStatus' || $state_name == 'powerSupplyStatus' || $state_name == 'intrusionStatus') {
                $states = array(
                    array($state_index_id,'other',0,1,3) ,
                    array($state_index_id,'unknown',0,2,3) ,
                    array($state_index_id,'ok',0,3,0) ,
                    array($state_index_id,'nonCritical',0,4,1) ,
                    array($state_index_id,'critical',0,5,2) ,
                    array($state_index_id,'nonRecoverable',0,6,2)
                );
            } elseif ($state_name == 'controllerState') {
                $states = array(
                    array($state_index_id,'ready',0,1,0) ,
                    array($state_index_id,'failed',0,2,2) ,
                    array($state_index_id,'online',0,3,0) ,
                    array($state_index_id,'offline',0,4,1) ,
                    array($state_index_id,'degraded',0,6,2)
                );
            } elseif ($state_name == 'arrayDiskState') {
                $states = array(
                    array($state_index_id,'ready',0,1,0) ,
                    array($state_index_id,'failed',0,2,2) ,
                    array($state_index_id,'online',0,3,0) ,
                    array($state_index_id,'offline',0,4,2) ,
                    array($state_index_id,'degraded',0,5,2) ,
                    array($state_index_id,'recovering',0,6,1) ,
                    array($state_index_id,'removed',0,7,1) ,
                    array($state_index_id,'non-raid',0,8,3) ,
                    array($state_index_id,'notReady',0,9,1) ,
                    array($state_index_id,'resynching',0,10,1) ,
                    array($state_index_id,'replacing',0,11,1) ,
                    array($state_index_id,'spinningDown',0,12,1) ,
                    array($state_index_id,'rebuild',0,13,1) ,
                    array($state_index_id,'noMedia',0,14,1) ,
                    array($state_index_id,'formatting',0,15,1) ,
                    array($state_index_id,'diagnostics',0,16,1) ,
                    array($state_index_id,'predictiveFailure',0,17,2) ,
                    array($state_index_id,'initializing',0,18,1) ,
                    array($state_index_id,'foreign',0,19,1) ,
                    array($state_index_id,'clear',0,20,1) ,
                    array($state_index_id,'unsupported',0,21,2) ,
                    array($state_index_id,'incompatible',0,22,2) ,
                    array($state_index_id,'readOnly',0,23,2)
                );
            } elseif ($state_name == 'virtualDiskState') {
                $states = array(
                    array($state_index_id,'unknown',0,0,3) ,
                    array($state_index_id,'ready',1,1,0) ,
                    array($state_index_id,'failed',1,2,2) ,
                    array($state_index_id,'online',1,3,1) ,
                    array($state_index_id,'offline',1,4,2) ,
                    array($state_index_id,'degraded',1,6,2) ,
                    array($state_index_id,'verifying',1,7,1) ,
                    array($state_index_id,'resynching',1,15,1) ,
                    array($state_index_id,'regenerating',1,16,1) ,
                    array($state_index_id,'failedRedundancy',1,18,2) ,
                    array($state_index_id,'rebuilding',1,24,1) ,
                    array($state_index_id,'formatting',1,26,1) ,
                    array($state_index_id,'reconstructing',1,32,1) ,
                    array($state_index_id,'initializing',1,35,1) ,
                    array($state_index_id,'backgroundInit',1,36,1) ,
                    array($state_index_id,'permanentlyDegraded',1,52,2)
                );
            } elseif ($state_name == 'batteryState') {
                $states = array(
                    array($state_index_id,'ready',0,1,0) ,
                    array($state_index_id,'failed',1,2,2) ,
                    array($state_index_id,'degraded',1,6,2) ,
                    array($state_index_id,'reconditioning',1,7,1) ,
                    array($state_index_id,'high',1,9,1) ,
                    array($state_index_id,'low',1,10,1) ,
                    array($state_index_id,'charging',1,12,1) ,
                    array($state_index_id,'missing',1,21,2) ,
                    array($state_index_id,'learning',1,36,1)
                );
            }

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

        foreach ($temp as $index => $entry) {
            if (strpos($index, '54.') === false) { //Because Dell is buggy
                if ($state_name == 'intrusionStatus') {
                    $descr = $tablevalue[3];
                } elseif ($state_name == 'batteryState') {
                    $descr = str_replace('"', "", snmp_get($device, "batteryConnectionControllerName." .$index. "", "-Ovqn", $tablevalue[4])) . ' - ' .$temp[$index][$tablevalue[3]];
                } elseif ($state_name == 'arrayDiskState') {
                    $descr = str_replace('"', "", snmp_get($device, "arrayDiskEnclosureConnectionEnclosureName." .$index. "", "-Ovqn", $tablevalue[4])) . ' - ' .$temp[$index][$tablevalue[3]];
                } else {
                    $descr = clean($temp[$index][$tablevalue[3]]); // Use clean as virtualDiskDeviceName is user defined
                }
                //Discover Sensors
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}
