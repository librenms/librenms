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

if ($device['os'] == 'drac') {
    $tables = array(
        array('virtualDiskTable','.1.3.6.1.4.1.674.10892.5.5.1.20.140.1.1.4.','virtualDiskState','virtualDiskName') ,
        array('processorDeviceTable','.1.3.6.1.4.1.674.10892.5.4.1100.30.1.5.','processorDeviceStatus','processorDeviceBrandName') ,
        array('memoryDeviceTable','.1.3.6.1.4.1.674.10892.5.4.1100.50.1.5.','memoryDeviceStatus','memoryDeviceLocationName') ,
        array('voltageProbeTable','.1.3.6.1.4.1.674.10892.5.4.600.20.1.5.','voltageProbeStatus','voltageProbeLocationName') ,
        array('amperageProbeTable','.1.3.6.1.4.1.674.10892.5.4.600.30.1.5.','amperageProbeStatus','amperageProbeLocationName') ,
        array('systemBatteryTable','.1.3.6.1.4.1.674.10892.5.4.600.50.1.5.','systemBatteryStatus','systemBatteryLocationName')

    );

    foreach ($tables as $tablevalue) {
        $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], array(), 'IDRAC-MIB-SMIv2');
        $cur_oid = $tablevalue[1];

        if (is_array($temp)) {
            //Create State Index
            $state_name = $tablevalue[2];
            $state_index_id = create_state_index($state_name);

            //Create State Translation
            if ($state_index_id !== null) {
                if ($state_name == 'virtualDiskState') {
                    $states = array(
                        array($state_index_id,'unknown',0,1,3) ,
                        array($state_index_id,'online',0,2,0) ,
                        array($state_index_id,'failed',0,3,2) ,
                        array($state_index_id,'degraded',0,4,1)
                     );
                } elseif ($state_name == 'processorDeviceStatus' || $state_name == 'memoryDeviceStatus') {
                    $states = array(
                        array($state_index_id,'other',0,1,3) ,
                        array($state_index_id,'unknown',0,2,3) ,
                        array($state_index_id,'ok',0,3,0) ,
                        array($state_index_id,'nonCritical',0,4,1) ,
                        array($state_index_id,'critical',0,5,2) ,
                        array($state_index_id,'nonRecoverable',0,6,2)
                    );
                } elseif ($state_name == 'voltageProbeStatus' || $state_name == 'amperageProbeStatus' || $state_name == 'systemBatteryStatus') {
                    $states = array(
                        array($state_index_id,'other',0,1,3) ,
                        array($state_index_id,'unknown',0,2,3) ,
                        array($state_index_id,'ok',0,3,0) ,
                        array($state_index_id,'nonCriticalUpper',0,4,1) ,
                        array($state_index_id,'criticalUpper',0,5,2) ,
                        array($state_index_id,'nonRecoverableUpper',0,6,2) ,
                        array($state_index_id,'nonCriticalLower',0,7,1) ,
                        array($state_index_id,'criticalLower',0,8,2) ,
                        array($state_index_id,'nonRecoverableLower',0,9,2) ,
                        array($state_index_id,'failed',0,10,2)
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
                if ($state_name == 'memoryDeviceStatus') {
                    $descr = $temp[$index][$tablevalue[3]] . ', ' . $temp[$index]['memoryDeviceSize']/1024 . ' MB';
                } else {
                    $descr = $temp[$index][$tablevalue[3]];
                }
                //Discover Sensors
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}
