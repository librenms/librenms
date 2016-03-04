<?php

// AVTECH TEMPPAGER
if ($device['os'] == 'avtech') {
    echo 'AVTECH: ';
    if (strpos($device['sysObjectID'], '.20916.1.7') !== false) {
    //  TemPageR 3E
        $device_oid = '.1.3.6.1.4.1.20916.1.7.';

        $internal = array(
            'id'        => 1,
            'oid'       => $device_oid.'1.1.1.1.0',
            'descr_oid' => $device_oid.'1.1.2.0',
        );
        avtech_add_temperature($device, $internal);

        $sen1 = array(
            'id'        => 2,
            'oid'       => $device_oid.'1.2.1.1.0',
            'descr_oid' => $device_oid.'1.2.1.3.0',
        );
        avtech_add_temperature($device, $sen1);

        $sen2 = array(
            'id'        => 3,
            'oid'       => $device_oid.'1.2.2.1.0',
            'descr_oid' => $device_oid.'1.2.2.3.0',
        );
        avtech_add_temperature($device, $sen2);
    }
    else if(strpos($device['sysObjectID'], '.20916.1.1') !== false) {
    //  TemPageR 4E
        $device_oid = '.1.3.6.1.4.1.20916.1.1.';

        $internal = array(
            'id'        => 1,
            'oid'       => $device_oid.'1.1.1.0',
            'descr'     => 'Internal',
            'max_oid'   => $device_oid.'3.1.0',
            'min_oid'   => $device_oid.'3.2.0',
        );
        avtech_add_temperature($device, $internal);

        $sen1 = array(
            'id'        => 2,
            'oid'       => $device_oid.'1.1.2.0',
            'descr'     => 'Sensor 1',
            'max_oid'   => $device_oid.'3.3.0',
            'min_oid'   => $device_oid.'3.4.0',
        );
        avtech_add_temperature($device, $sen1);

        $sen2 = array(
            'id'        => 3,
            'oid'       => $device_oid.'1.1.3.0',
            'descr'     => 'Sensor 2',
            'max_oid'   => $device_oid.'3.5.0',
            'min_oid'   => $device_oid.'3.6.0',
        );
        avtech_add_temperature($device, $sen2);

        $sen3 = array(
            'id'        => 4,
            'oid'       => $device_oid.'1.1.4.0',
            'descr'     => 'Sensor 3',
            'max_oid'   => $device_oid.'3.7.0',
            'min_oid'   => $device_oid.'3.8.0',
        );
        avtech_add_temperature($device, $sen3);
    }
    else if(strpos($device['sysObjectID'], '.20916.1.6') !== false) {
    //  RoomAlert 4E
        $device_oid = '.1.3.6.1.4.1.20916.1.6.';
        $divisor = 1;

        $internal = array(
            'id'        => 1,
            'oid'       => $device_oid.'1.1.1.2.0',
            'descr_oid' => $device_oid.'1.1.2.1.0',
            'divisor'   => $divisor,
        );
        avtech_add_temperature($device, $internal);

        $sen1 = array(
            'id'        => 2,
            'oid'       => $device_oid.'1.2.2.1.0',
            'descr_oid' => $device_oid.'1.2.1.6.0',
            'divisor'   => $divisor,
        );
        avtech_add_temperature($device, $sen1);

        $sen2 = array(
            'id'        => 3,
            'oid'       => $device_oid.'1.2.2.1.0',
            'descr_oid' => $device_oid.'1.2.2.6.0',
            'divisor'   => $divisor,
        );
        avtech_add_temperature($device, $sen2);

/* we don't support switches at this time
        $switch = array(
            'oid'       => $device_oid.'1.3.1.0',
            'descr_oid' => $device_oid.'1.3.2.0',
        );
        avtech_add_switch($device, $switch);
*/
    }
}//end if



/**
 * Helper function to improve readability
 * Can't use mib based polling, because the snmp implentation and mibs are terrible
 *
 * @param (device) array - device array
 * @param (valid) array - valid array
 * @param (sensor) array(id, oid, descr, descr_oid, min, max, divisor)
 */
function avtech_add_temperature($device, $sensor) {
    global $valid;

    // set the id, must be unique
    if ($sensor['id']) {
        $id = $sensor['id'];
    }
    else {
        d_echo('Error: No id set for this sensor' . "\n");
        return false;
    }
    d_echo('Sensor id: ' . $id . "\n");


    // set the sensor oid
    if ($sensor['oid']) {
        $oid = $sensor['oid'];
    }
    else {
        d_echo('Error: No oid set for this sensor' . "\n");
        return false;
    }
    d_echo('Sensor oid: ' . $oid . "\n");

    // get the sensor value
    $value = snmp_get($device, $oid, '-OvQ');
    // if the sensor doesn't exist abort
    if ($value === false || $value == 0) {  //issue unfortunately non-existant sensors return 0
        d_echo('Error: sensor returned no data, skipping' . "\n");
        return false;
    }
    d_echo('Sensor value: ' . $value . "\n");

    // set the description
    if ($sensor['descr_oid']) {
        $descr = snmp_get($device, $sensor['descr_oid'], '-OvQ');
    }
    elseif ($sensor['descr']) {
        $descr = $sensor['descr'];
    }
    else {
        d_echo('Error: No description set for this sensor' . "\n");
        return false;
    }
    d_echo('Sensor description: ' . $descr . "\n");

    // set divisor
    if ($sensor['divisor']) {
        $divisor = $sensor['divisor'];
    }
    else {
        $divisor = 100;
    }
    d_echo('Sensor divisor: ' . $divisor . "\n");


    // set min for alarm
    if ($sensor['min_oid']) {
        $min = snmp_get($device, $sensor['min_oid'], '-OvQ') / $divisor;
    }
    else {
        $min = null;
    }
    d_echo('Sensor alarm min: ' . $min . "\n");

    // set max for alarm
    if ($sensor['max_oid']) {
        $max = snmp_get($device, $sensor['max_oid'], '-OvQ') / $divisor;
    }
    else {
        $max = null;
    }
    d_echo('Sensor alarm max: ' . $max . "\n");

    // add the sensor
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $id, $device['os'], $descr, $divisor, '1', $min, null, null, $max, $value/$divisor);
    return true;
}
