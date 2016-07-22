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
    $state_name = 'ibm-amm_BlowerState';
    $state_descr = 'Blower ';
    $oids = array(
        '.1.3.6.1.4.1.2.3.51.2.2.3.10.0', // BLADE-MIB::blower1State.0
        '.1.3.6.1.4.1.2.3.51.2.2.3.11.0', // BLADE-MIB::blower2State.0
        '.1.3.6.1.4.1.2.3.51.2.2.3.12.0', // BLADE-MIB::blower3State.0
        '.1.3.6.1.4.1.2.3.51.2.2.3.13.0', // BLADE-MIB::blower4State.0
    );
    /* BLADE-MIB: blower1State
     *  unknown(0),
     *  good(1),
     *  warning(2),
     *  bad(3)
     */

    foreach ($oids as $oid) {
        $state = snmp_get($device, $oid, '-Oqv');
        $descr = $state_descr . $index;

        if (!empty($state)) {

            $state_index_id = create_state_index($state_name);
            
            if ($state_index_id) {

                $states = array(
                    array($state_index_id, 'unknown', 0, 0, 3),
                    array($state_index_id, 'good', 1, 1, 0),
                    array($state_index_id, 'warning', 1, 2, 1),
                    array($state_index_id, 'bad', 1, 3, 2),
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

            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', $index);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
            $index++;

        }//end if

    }//end foreach

    $index = 1;
    $state_name = 'ibm-amm_BlowerControllerState';
    $state_descr = 'Blower Controller ';
    $oids = array(
        '.1.3.6.1.4.1.2.3.51.2.2.3.30.0', // BLADE-MIB::blower1ControllerState.0
        '.1.3.6.1.4.1.2.3.51.2.2.3.31.0', // BLADE-MIB::blower2ControllerState.0
        '.1.3.6.1.4.1.2.3.51.2.2.3.32.0', // BLADE-MIB::blower3ControllerState.0
        '.1.3.6.1.4.1.2.3.51.2.2.3.33.0', // BLADE-MIB::blower4ControllerState.0
        );

    /* BLADE-MIB: blower1ControllerState
     *  operational(0),
     *  flashing(1),
     *  notPresent(2),
     *  communicationError(3),
     *  unknown(255)
     */
    foreach ($oids as $oid) {
        $state = snmp_get($device, $oid, '-Oqv');
        $descr = $state_descr . $index;

        if (is_numeric($state) && $state != 2) {

            $state_index_id = create_state_index($state_name);

            if ($state_index_id) {

                $states = array(
                    array($state_index_id, 'operational', 1, 0, 0),
                    array($state_index_id, 'flashing', 1, 1, 1),
                    array($state_index_id, 'notPresent', 1, 2, -1),
                    array($state_index_id, 'communicationError', 1, 3, 2),
                    array($state_index_id, 'unknown', 0, 255, 3),
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

            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', $index);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
            $index++;

        }//end if

    }//end foreach

    $index = 1;
    $state_name = 'ibm-amm_PowerModuleState';
    $state_descr = 'Power Module ';
    $powerModuleStateOid= '.1.3.6.1.4.1.2.3.51.2.2.4.1.1.3'; // BLADE-MIB::powerModuleState
    $data = snmpwalk_cache_oid_num($device, $powerModuleStateOid, array());

    /*  BLADE-MIB: powerModuleState
     *   unknown(0),
     *   good(1),
     *   warning(2),
     *   notAvailable(3),
     *   critical(4)
     */
    foreach ($data as $oid => $array) {
        $state = current($array);  // get the first (and only) item from the array
        $descr = $state_descr . $index;

        if (is_numeric($state) && $state != 3) {

            $state_index_id = create_state_index($state_name);

            if ($state_index_id) {

                $states = array(
                    array($state_index_id, 'unknown', 0, 0, 3),
                    array($state_index_id, 'good', 1, 1, 0),
                    array($state_index_id, 'warning', 1, 2, 1),
                    array($state_index_id, 'notAvailable', 1, 3, -1),
                    array($state_index_id, 'critical', 1, 4, 2),
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
                }//end foreach

            }//end if

            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null,
                null, null, $state, 'snmp', $index);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
            $index++;

        }//end if
    }//end foreach

}//end if
