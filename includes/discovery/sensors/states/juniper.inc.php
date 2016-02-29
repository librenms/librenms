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

    $temp = snmpwalk_cache_multi_oid($device, 'jnxFruTable', array(), 'JUNIPER-MIB');
    $cur_oid = '.1.3.6.1.4.1.2636.3.1.15.1.8.';

    if (is_array($temp)) {
        //Create State Index
        $state_name = 'jnxFruState';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id) {
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
            foreach($states as $value){ 
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
            $descr = $temp[$index]['jnxFruName'];
            if (stripos($descr, 'cb') !== false || stripos($descr, 'pem') !== false || stripos($descr, 'fan') !== false || stripos($descr, 'power') !== false || preg_match('/Routing Engine [0|1]/', $descr)) {

                //Discover Sensors
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index]['jnxFruState'], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}
