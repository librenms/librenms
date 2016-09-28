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

if ($device['os'] == 'junos') {
    $tables = array(
        array('JUNIPER-MIB','jnxFruTable','.1.3.6.1.4.1.2636.3.1.15.1.8.','jnxFruState','jnxFruName') ,
        array('JUNIPER-ALARM-MIB','jnxYellowAlarms','.1.3.6.1.4.1.2636.3.4.2.2.1.0','jnxYellowAlarmState') ,
        array('JUNIPER-ALARM-MIB','jnxRedAlarms','.1.3.6.1.4.1.2636.3.4.2.3.1.0','jnxRedAlarmState')
    );

    foreach ($tables as $tablevalue) {
        $temp = snmpwalk_cache_multi_oid($device, $tablevalue[1], array(), $tablevalue[0], 'junos');
        $cur_oid = $tablevalue[2];

        if (is_array($temp)) {
            //Create State Index
            $state_name = $tablevalue[3];
            $state_index_id = create_state_index($state_name);

            //Create State Translation
            if ($state_index_id !== null) {
                if ($state_name == 'jnxFruState') {
                    $states = array(
                        array($state_index_id,'unknown',0,1,3) ,
                        array($state_index_id,'empty',0,2,3) ,
                        array($state_index_id,'present',0,3,1) ,
                        array($state_index_id,'ready',0,4,0) ,
                        array($state_index_id,'announceOnline',0,5,0) ,
                        array($state_index_id,'online',0,6,0) ,
                        array($state_index_id,'anounceOffline',0,7,1) ,
                        array($state_index_id,'offline',0,8,2) ,
                        array($state_index_id,'diagnostic',0,9,3) ,
                        array($state_index_id,'standby',0,10,3)
                    );
                } else {
                    $states = array(
                        array($state_index_id,'other',0,1,3) ,
                        array($state_index_id,'off',0,2,0) ,
                        array($state_index_id,'on',0,3,2)
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
                $oid = $cur_oid.$index;
                $descr = $temp[$index]['jnxFruName'];
                
                if ($tablevalue[3] !== 'jnxFruState') {
                    $oid = $tablevalue[2];
                    $descr = 'Yellow Alarm';
                    if ($tablevalue[3] == 'jnxRedAlarmState') {
                        $descr = 'Red Alarm';
                    }
                }
                if (stripos($descr, 'Yellow Alarm') !== false || stripos($descr, 'Red Alarm') !== false || stripos($descr, 'cb') !== false || stripos($descr, 'pem') !== false || stripos($descr, 'fan') !== false || stripos($descr, 'power') !== false || preg_match('/Routing Engine [0|1]/', $descr)) {
                    //Discover Sensors
                    discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[3]], 'snmp', $index);

                    //Create Sensor To State Index
                    create_sensor_to_state_index($device, $state_name, $index);
                }
            }
        }
    }
}
