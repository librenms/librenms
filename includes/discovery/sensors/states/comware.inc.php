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

if ($device['os'] == 'comware') {
    $tables = array(
        array('hh3cdevMFanStatusTable','.1.3.6.1.4.1.25506.8.35.9.1.1.1.2.','hh3cDevMFanStatus') ,
        array('hh3cdevMPowerStatusTable','.1.3.6.1.4.1.25506.8.35.9.1.2.1.2.','hh3cDevMPowerStatus')
    );

    foreach ($tables as $tablevalue) {
        $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], array(), 'HH3C-LswDEVM-MIB');
        $cur_oid = $tablevalue[1];

        if (is_array($temp)) {
            //Create State Index
            $state_name = $tablevalue[2];
            $state_index_id = create_state_index($state_name);

            //Create State Translation
            if ($state_index_id !== null) {
                $states = array(
                     array($state_index_id,'active',0,1,0) ,
                     array($state_index_id,'deactive',0,2,2) ,
                     array($state_index_id,'not-install',0,3,3) ,
                     array($state_index_id,'unsupport',0,4,1)
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
                //Discover Sensors
                $descr = 'Fan ' . $index;
                if ($entry['hh3cDevMPowerStatus']) {
                    $descr = 'Power Supply ' . $index;
                }
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}
