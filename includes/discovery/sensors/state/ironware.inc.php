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

$temp = snmpwalk_cache_multi_oid($device, 'snChasPwrSupplyTable', array(), 'FOUNDRY-SN-AGENT-MIB');
$cur_oid = '.1.3.6.1.4.1.1991.1.1.1.2.1.1.3.';

if (is_array($temp)) {
    //Create State Index
    $state_name = 'snChasPwrSupplyOperStatus';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'other',0,1,3) ,
            array($state_index_id,'normal',0,2,0) ,
            array($state_index_id,'failure',0,3,2)
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
        $descr = $temp[$index]['snChasPwrSupplyDescription'];
        if (empty($descr)) {
            $descr = "Power Supply " . $index;
        }
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index]['snChasPwrSupplyOperStatus'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
