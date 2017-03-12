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
    array('systemHealth.0','.1.3.6.1.4.1.17163.1.1.2.7.0','systemHealth','System Health') ,
    array('optServiceStatus.0','.1.3.6.1.4.1.17163.1.1.2.8.0','optServiceStatus','Optimization Service Status')
);

foreach ($tables as $tablevalue) {
    $temp = snmp_get($device, $tablevalue[0], "-Ovqe", "STEELHEAD-MIB");
    $oid = $tablevalue[1];

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = $tablevalue[2];
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            if ($state_name == 'systemHealth') {
                $states = array(
                    array($state_index_id,'healthy',0,10000,0) ,
                    array($state_index_id,'degraded',0,30000,1) ,
                    array($state_index_id,'admissionControl',0,31000,1) ,
                    array($state_index_id,'critical',0,50000,2)
                );
            } else {
                $states = array(
                    array($state_index_id,'none',0,0,3) ,
                    array($state_index_id,'unmanaged',0,1,3) ,
                    array($state_index_id,'running',0,2,0) ,
                    array($state_index_id,'sentCom1',0,3,1) ,
                    array($state_index_id,'sentTerm1',0,4,1) ,
                    array($state_index_id,'sentTerm2',0,5,1) ,
                    array($state_index_id,'sentTerm3',0,6,1) ,
                    array($state_index_id,'pending',0,7,1) ,
                    array($state_index_id,'stopped',0,8,2)
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
        $descr = $tablevalue[3];
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $tablevalue[0]);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
