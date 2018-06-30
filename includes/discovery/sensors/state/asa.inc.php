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

$temp = snmpwalk_cache_multi_oid($device, 'cfwHardwareStatusTable', array(), 'CISCO-FIREWALL-MIB');
$cur_oid = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.3.';

if (is_array($temp)) {
    //Create State Index
    if (strstr($temp['netInterface']['cfwHardwareStatusDetail'], 'not Configured') == false) {
        $state_name = 'cfwHardwareStatus';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'other',0,1,2) ,
                array($state_index_id,'up',0,2,0) ,
                array($state_index_id,'down',0,3,2) ,
                array($state_index_id,'error',0,4,2) ,
                array($state_index_id,'overTemp',0,5,2) ,
                array($state_index_id,'busy',0,6,2) ,
                array($state_index_id,'noMedia',0,7,2) ,
                array($state_index_id,'backup',0,8,2) ,
                array($state_index_id,'active',0,9,0) ,
                array($state_index_id,'standby',0,10,0)
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
            $descr = ucwords(trim(preg_replace('/\s*\([^\s)]*\)/', '', $temp[$index]['cfwHardwareInformation'])));
            if ($index == 'netInterface') {
                $index = 4;
            } elseif ($index == 'primaryUnit') {
                $index = 6;
            } elseif ($index == 'secondaryUnit') {
                $index = 7;
            }
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][' cfwHardwareStatusValue'], 'snmp', $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
