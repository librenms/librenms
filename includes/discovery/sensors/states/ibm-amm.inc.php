<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'ibm-amm') {

    $index = 1;
    $oids = array('blower1State','blower2State','blower3State','blower4State');

    foreach ($oids as $oid) {

        $state = snmp_get($device, $oid.'.0', '-OsqnU', 'BLADE-MIB');

        if (!empty($state)) {

            $state_name = $oid;
            $state_index_id = create_state_index($state_name);
            
            if ($state_index_id) {

                $states = array(
                    array($state_index_id,'uknown',0,1,3) ,
                    array($state_index_id,'good',1,2,0) ,
                    array($state_index_id,'warning',1,3,1) ,
                    array($state_index_id,'bad',1,4,2) ,
                );
 
                foreach($states as $value) { 
                    $insert = array(
                        'state_index_id' => $value[0],
                        'state_descr' => $value[1],
                        'state_draw_graph' => $value[2],
                        'state_value' => $value[3],
                        'state_generic_value' => $value[4]
                    );
                    dbInsert($insert, 'state_translations');
                }//end foreach

            }//end if

            discover_sensor($valid['sensor'], 'state', $device, $oid, 0, $state_name, $state_name, '1', '1', null, null, null, null, $state, 'snmp', $index);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
            $index++;

        }//end if

    }//end foreach

    $index = 1;
    $oids = array('blower1ControllerState','blower2ControllerState','blower3ControllerState','blower4ControllerState');

    foreach ($oids as $oid) {

        $state = snmp_get($device, $oid.'.0', '-OsqnU', 'BLADE-MIB');

        if (!empty($state)) {

            $state_name = $oid;
            $state_index_id = create_state_index($state_name);

            if ($state_index_id) {

                $states = array(
                    array($state_index_id,'operational',0,0,0),
                    array($state_index_id,'flashing',1,1,1),
                    array($state_index_id,'notPresent',1,2,2),
                    array($state_index_id,'communicationError',1,3,2),
                    array($state_index_id,'unknown',1,4,2),
                );

                foreach($states as $value) {
                    $insert = array(
                        'state_index_id' => $value[0],
                        'state_descr' => $value[1],
                        'state_draw_graph' => $value[2],
                        'state_value' => $value[3],
                        'state_generic_value' => $value[4]
                    );
                    dbInsert($insert, 'state_translations');
                }//end foreach

            }//end if

            discover_sensor($valid['sensor'], 'state', $device, $oid, 0, $state_name, $state_name, '1', '1', null, null, null, null, $state, 'snmp', $index);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
            $index++;

        }//end if

    }//end foreach

}//end if
