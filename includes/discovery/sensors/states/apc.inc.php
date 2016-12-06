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

if ($device['os'] == 'apc') {
    $temp = snmp_get($device, "upsAdvBatteryReplaceIndicator.0", "-Ovqe", "PowerNet-MIB");
    $cur_oid = '.1.3.6.1.4.1.318.1.1.1.2.2.4.0';
    $index = '0';

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'upsAdvBatteryReplaceIndicator';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'noBatteryNeedsReplacing',0,1,0) ,
                array($state_index_id,'batteryNeedsReplacing',0,2,2)
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

        $descr = 'UPS Battery Replacement Status';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
