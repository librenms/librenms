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

if ($device['os'] == 'ddnos') {
    $tables = array(
        array('tempTable','.1.3.6.1.4.1.6894.2.2.1.4.','tempStatus') ,
        array('fanTable','.1.3.6.1.4.1.6894.2.4.1.4.','fanStatus') ,
        array('powerTable','.1.3.6.1.4.1.6894.2.6.1.4.','powerStatus') ,
        array('physicalDiskTable','.1.3.6.1.4.1.6894.2.9.1.7.','physDiskState')
    );

    foreach ($tables as $tablevalue) {
        $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], array(), 'SFA-INFO');
        $cur_oid = $tablevalue[1];

        if (is_array($temp)) {
            //Create State Index
            $state_name = $tablevalue[2];
            $state_index_id = create_state_index($state_name);

            //Create State Translation
            if ($state_index_id !== null) {
                if ($state_name == 'fanStatus' || $state_name == 'powerStatus') {
                    $states = array(
                        array($state_index_id,'healthy',0,1,0) ,
                        array($state_index_id,'failure',0,2,2)
                    );
                } elseif ($state_name == 'tempStatus') {
                    $states = array(
                        array($state_index_id,'normal',0,1,0) ,
                        array($state_index_id,'warning',0,2,1) ,
                        array($state_index_id,'critical',0,3,2)
                    );
                } else {
                    $states = array(
                        array($state_index_id,'normal',0,1,0) ,
                        array($state_index_id,'failed',0,2,2) ,
                        array($state_index_id,'predictedfailure',0,3,1) ,
                        array($state_index_id,'unknown',0,4,3)
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
                //Discover Sensors
                $descr = 'Temperature Sensor ' . $index;
                if ($entry['fanStatus']) {
                    $descr = 'Fan Sensor ' . $index;
                } elseif ($entry['powerStatus']) {
                    $descr = 'Power Supply Sensor ' . $index;
                } elseif ($entry['physDiskState']) {
                    $descr = 'Disk Sensor ' . $index;
                }
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}
