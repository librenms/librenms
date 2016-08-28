<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Tony Murray <murrayton@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'netonix') {
    $temp = snmpwalk_cache_multi_oid($device, '.1.3.6.1.4.1.46242.5.1.2', array());
    $cur_oid = '.1.3.6.1.4.1.';

    if (is_array($temp)) {
        //Create State Index
        $state_name = 'netonixPoeStatus';
        $state_index_id = create_state_index($state_name);

        $states_ids = array(
            'Off' => 1,
            '24V' => 2,
            '48V' => 3
        );

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'Off',0,1,-1) ,
                array($state_index_id,'24V',0,2,0) ,
                array($state_index_id,'48V',0,3,1) ,
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

        foreach ($temp as $index => $entry) {
            $id = substr($index, strrpos($index, '.')+1);
            $descr = 'Port ' . $id . ' PoE';
            $current = $states_ids[$entry['enterprises']];
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $id, $state_name, $descr, '1', '1', null, null, null, null, $current);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $id);
        }
    }
}
