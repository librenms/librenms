<?php

/**
 *
 * For ZTT MSJ devices
 *
 */


// DC over voltage alarm
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.20.1', '-Ovqe');
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'DCovervoltagealarm';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 0;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.1.1.1.20.1',
        $sensor_index,
        $state_name,
        'DCovervoltage Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}

// DC voltage low alarm
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.21.1', '-Ovqe');
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'DCvoltagelowalarm';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 1;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.1.1.1.21.1',
        $sensor_index,
        $state_name,
        'DCvoltagelow Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}
// Load fuse broken
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.23.1', '-Ovqe');
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'Loadfusebroken';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 2;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.1.1.1.23.1',
        $sensor_index,
        $state_name,
        'Loadfusebroken Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}
// Battery pack fuse blown
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.24.1', '-Ovqe');
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'Batterypackfuseblown';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 3;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.1.1.1.24.1',
        $sensor_index,
        $state_name,
        'Batterypackfuse blown Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}
// AC power failure alarm
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.2.1.1.23.1', '-Ovqe');
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'ACpowerfailurealarm';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 4;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.2.1.1.23.1',
        $sensor_index,
        $state_name,
        'ACpowerfailure Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}
//Rectifier module alarm - Rectifiermodule01
//Note: ZTT MSJ device state 0 = faulty, 1 = normal
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.4.1.1.49.1', '-Ovqe');
if (is_numeric($state)) {

    if ($state == 1) {
        $state_op = 0;
    } else {
        $state_op = 1;
    }
    //Create State Index
    $state_name = 'Rectifiermodulealarm';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 5;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.4.1.1.49.1',
        $sensor_index,
        $state_name,
        'Rectifiermodule01 PresentStatus',
        1,
        1,
        null,
        null,
        null,
        null,
        $state_op,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}
//Rectifier module alarm - Rectifiermodule02
//Note: ZTT MSJ device state 0 = faulty, 1 = normal
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.4.1.1.50.1', '-Ovqe');
if (is_numeric($state)) {
    //Create State Index
    if ($state == 1) {
        $state_op = 0;
    } else {
        $state_op = 1;
    }
    $state_name = 'Rectifiermodulealarm';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 6;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.4.1.1.50.1',
        $sensor_index,
        $state_name,
        'Rectifiermodule02 PresentStatus',
        1,
        1,
        null,
        null,
        null,
        null,
        $state_op,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}

//Rectifier module alarm - Rectifiermodule03
//Note: ZTT MSJ device state 0 = faulty, 1 = normal
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.4.1.1.51.1', '-Ovqe');
if (is_numeric($state)) {
    //Create State Index
    if ($state == 1) {
        $state_op = 0;
    } else {
        $state_op = 1;
    }
    $state_name = 'Rectifiermodulealarm';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 7;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.4.1.1.51.1',
        $sensor_index,
        $state_name,
        'Rectifiermodule03 PresentStatus',
        1,
        1,
        null,
        null,
        null,
        null,
        $state_op,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}
//Rectifier module alarm - Rectifiermodule04
//Note: ZTT MSJ device state 0 = faulty, 1 = normal
$state = snmp_get($device, '.1.3.6.1.4.1.49692.1.4.1.1.52.1', '-Ovqe');
if (is_numeric($state)) {
    //Create State Index
    if ($state == 1) {
        $state_op = 0;
    } else {
        $state_op = 1;
    }
    $state_name = 'Rectifiermodulealarm';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Faulty'],
        ]
    );
    $sensor_index = 8;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.49692.1.4.1.1.52.1',
        $sensor_index,
        $state_name,
        'Rectifiermodule04 PresentStatus',
        1,
        1,
        null,
        null,
        null,
        null,
        $state_op,
        'snmp',
        $sensor_index
    );
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}