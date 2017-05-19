<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$temp = snmpwalk_cache_multi_oid($device, 'otherStateSensorTable', array(), 'NETBOTZ410-MIB');
$cur_oid = '.1.3.6.1.4.1.5528.100.4.2.10.1.3.';

if (is_array($temp)) {
    //Create State Index
    $state_name = 'otherStateSensorErrorStatus';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id, 'normal', 0, 0, 0),
            array($state_index_id, 'info', 0, 1, 1),
            array($state_index_id, 'warning', 0, 2, 1),
            array($state_index_id, 'error', 0, 3, 2),
            array($state_index_id, 'critical', 0, 4, 2),
            array($state_index_id, 'failure', 0, 5, 2)
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
        $descr = $temp[$index]['otherStateSensorLabel'];
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][' otherStateSensorErrorStatus'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}


foreach ($pre_cache['netbotz_state'] as $index => $data) {
    if (is_array($data)) {
        $tmp_keys = array_keys($data);
        $state_name = str_replace('Id', '', $tmp_keys[0]);
        $state_index_id = create_state_index($state_name);
        if ($state_name === 'cameraMotionSensor') {
            $states = array(
                array($state_index_id, 'null', 0, -1, 3),
                array($state_index_id, 'noMotion', 0, 0, 0),
                array($state_index_id, 'motionDetected', 0, 1, 2),
            );
            $oid = '.1.3.6.1.4.1.5528.100.4.2.3.1.2.';
        } elseif ($state_name === 'doorSwitchSensor') {
            $states = array(
                array($state_index_id, 'null', 0, -1, 3),
                array($state_index_id, 'open', 0, 0, 0),
                array($state_index_id, 'closed', 0, 1, 2),
            );
            $oid = '.1.3.6.1.4.1.5528.100.4.2.2.1.2.';
        } elseif ($state_name === 'dryContactSensor') {
            $states = array(
                array($state_index_id, 'null', 0, -1, 3),
                array($state_index_id, 'open', 0, 0, 0),
                array($state_index_id, 'closed', 0, 1, 2),
            );
            $oid = '.1.3.6.1.4.1.5528.100.4.2.1.1.2.';
        }
        if ($state_index_id !== null) {
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
        $cur_oid = $oid . $index;
        $index   = $state_name . '.' . $index;
        $descr   = $data[$state_name . 'Label'];
        $value   = $data[$state_name . 'Value'];
        if (isset($value)) {
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $value);
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
