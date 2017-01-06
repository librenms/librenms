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

if ($device['os'] == 'dell-ups') {
    $temp = snmp_get($device, "productStatusGlobalStatus.0", "-Ovqe", "DELL-SNMP-UPS-MIB");
    $cur_oid = '.1.3.6.1.4.1.674.10902.2.110.1.0';
    $index = '0';

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'productStatusGlobalStatus';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'other',0,1,3) ,
                array($state_index_id,'unknown',0,2,3) ,
                array($state_index_id,'ok',0,3,0) ,
                array($state_index_id,'non-critical',0,4,1) ,
                array($state_index_id,'critical',0,5,2) ,
                array($state_index_id,'non-recoverable',0,6,2)
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

        $descr = 'Current Status';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
