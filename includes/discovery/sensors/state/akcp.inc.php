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

$temp = snmpwalk_cache_multi_oid($device, 'hhmsSensorArraySwitchStatus', array(), 'HHMSAGENT-MIB');
$cur_oid = '.1.3.6.1.4.1.3854.1.2.2.1.18.1.3.';

if (is_array($temp)) {
    //Create State Index
    $state_name = 'hhmsSensorArraySwitchStatus';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
                array($state_index_id,'noStatus',0,1,3) ,
                array($state_index_id,'normal',0,2,0) ,
                array($state_index_id,'critical',0,4,2) ,
                array($state_index_id,'sensorError',0,7,1)
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
        if ($temp[$index]['hhmsSensorArraySwitchStatus'] != 'noStatus') {
            $descr = trim(snmp_get($device, 'hhmsSensorArraySwitchDescription.'.$index, '-Oqv', 'HHMSAGENT-MIB'), '"');
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index]['hhmsSensorArraySwitchStatus'], 'snmp', $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
