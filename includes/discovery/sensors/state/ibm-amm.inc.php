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

$index = 1;
$state_name = 'ibm-amm_BlowerState';
$state_descr = 'Blower ';
$oids = [
    '.1.3.6.1.4.1.2.3.51.2.2.3.10.0', // BLADE-MIB::blower1State.0
    '.1.3.6.1.4.1.2.3.51.2.2.3.11.0', // BLADE-MIB::blower2State.0
    '.1.3.6.1.4.1.2.3.51.2.2.3.12.0', // BLADE-MIB::blower3State.0
    '.1.3.6.1.4.1.2.3.51.2.2.3.13.0', // BLADE-MIB::blower4State.0
];
/* BLADE-MIB: blower1State
 *  unknown(0),
 *  good(1),
 *  warning(2),
 *  bad(3)
*/

foreach ($oids as $oid) {
    $state = snmp_get($device, $oid, '-Oqv');
    $descr = $state_descr . $index;

    if (! empty($state)) {
        $states = [
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'good'],
            ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'warning'],
            ['value' => 3, 'generic' => 2, 'graph' => 1, 'descr' => 'bad'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $state, 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
        $index++;
    }//end if
}//end foreach

$index = 1;
$state_name = 'ibm-amm_BlowerControllerState';
$state_descr = 'Blower Controller ';
$oids = [
    '.1.3.6.1.4.1.2.3.51.2.2.3.30.0', // BLADE-MIB::blower1ControllerState.0
    '.1.3.6.1.4.1.2.3.51.2.2.3.31.0', // BLADE-MIB::blower2ControllerState.0
    '.1.3.6.1.4.1.2.3.51.2.2.3.32.0', // BLADE-MIB::blower3ControllerState.0
    '.1.3.6.1.4.1.2.3.51.2.2.3.33.0', // BLADE-MIB::blower4ControllerState.0
];

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
        $states = [
            ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'operational'],
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'flashing'],
            ['value' => 2, 'generic' => -1, 'graph' => 1, 'descr' => 'notPresent'],
            ['value' => 3, 'generic' => 2, 'graph' => 1, 'descr' => 'communicationError'],
            ['value' => 255, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $state, 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
        $index++;
    }//end if
}//end foreach

$index = 1;
$state_name = 'ibm-amm_PowerModuleState';
$state_descr = 'Power Module ';
$powerModuleStateOid = '.1.3.6.1.4.1.2.3.51.2.2.4.1.1.3'; // BLADE-MIB::powerModuleState
$data = snmpwalk_cache_oid_num($device, $powerModuleStateOid, []);

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
        $states = [
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'good'],
            ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'warning'],
            ['value' => 3, 'generic' => -1, 'graph' => 1, 'descr' => 'notAvailable'],
            ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'critical'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $state, 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
        $index++;
    }//end if
}//end foreach
