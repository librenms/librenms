<?php

echo 'RAy Racom State';

// System Status Ray1 and Ray2 (Value : na (0) unknown, ok (1) ok, warning (2) warning, alarm (3) alarm)
$state = snmp_get($device, "systemStatus", "-Ovqe", 'RAY-MIB');
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'systemStatus-old';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Ok'],
            ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Warning'],
            ['value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'Alarm'],
        ]
    );

    $sensor_index = 0;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.33555.1.1.3.1',
        $sensor_index,
        $state_name,
        'System Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        0
    );

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
} else {
// System Status Ray1 and Ray2 (Value : na (0) unknown, ok (1) ok, warning (2) warning, alarm (3) alarm)
    $state = snmp_get($device, "systemStatus.0", "-Ovqe", 'RAY-MIB');
    if (is_numeric($state)) {
    //Create State Index
        $state_name = 'systemStatus';
        create_state_index(
            $state_name,
            [
                ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Ok'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Warning'],
                ['value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'Alarm'],
            ]
        );

                $sensor_index = 0;
                discover_sensor(
                    $valid['sensor'],
                    'state',
                    $device,
                    '.1.3.6.1.4.1.33555.1.1.3.1.0',
                    $sensor_index,
                    $state_name,
                    'System Status',
                    1,
                    1,
                    null,
                    null,
                    null,
                    null,
                    $state,
                    'snmp',
                    0
                );

    //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $sensor_index);
    }
}






// Line Status (Value : na (0) unknown, ok (1) ok, analyzer (2) analyzer, connecting (3) connecting, searching (4)). Supported by RAy10 only.
$state = snmp_get($device, "lineStatus.0", "-Ovqe", 'RAY-MIB');
if ($state) {
    //Create State Index
    $state_name = 'lineStatus';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Ok'],
            ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Analyzer'],
            ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'Connecting'],
            ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'Searching'],
        ]
    );

    $sensor_index = 1;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.33555.1.1.3.2',
        $sensor_index,
        $state_name,
        'Radio Link Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        0
    );

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}

// RF Power Status (Value : na (0) unknown, ok (1) ok, fail (2) fail)
$state = snmp_get($device, "rfPowerStatus.0", "-Ovqe", 'RAY-MIB');
if ($state) {
    //Create State Index
    $state_name = 'rfPowerStatus';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Ok'],
            ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'Failure'],
        ]
    );

    $sensor_index = 2;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.33555.1.1.3.4',
        $sensor_index,
        $state_name,
        'RF Power Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        0
    );

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}

// Peer station user Ethernet link Status (Value : na (0) unknown, up (1) up, down (2) down) Not supported by RAy2.
$state = snmp_get($device, "ethPeer.0", "-Ovqe", 'RAY-MIB');
if ($state) {
    //Create State Index
    $state_name = 'ethPeer';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Up'],
            ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'Down'],
        ]
    );

    $sensor_index = 3;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.33555.1.1.3.6',
        $sensor_index,
        $state_name,
        'Peer Station Ethernet Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        0
    );

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}
//Tx Modulation state for Ray1 and Ray2
$state = snmp_get($device, "txModulationIndex", "-Ovqe", 'RAY-MIB');
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'txModulation-old';
    create_state_index(
        $state_name,
        [
            ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'NA'],
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Qpsk'],
            ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam16'],
            ['value' => 3, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam32'],
            ['value' => 4, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam64'],
            ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam128'],
            ['value' => 6, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam256'],
        ]
    );

    $sensor_index = 4;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.33555.1.2.1.8',
        $sensor_index,
        $state_name,
        'Tx Modulation Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        0
    );

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
} else {
    //Tx Modulation state for Ray3
    $state = snmp_get($device, "txModulationIndex.0", "-Ovqe", 'RAY-MIB');
    if (is_numeric($state)) {
    //Create State Index
        $state_name = 'txModulation';
        create_state_index(
            $state_name,
            [
            ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'NA'],
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Qpsk_s'],
            ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'Qpsk'],
            ['value' => 3, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam16'],
            ['value' => 4, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam32'],
            ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam64'],
            ['value' => 6, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam128'],
            ['value' => 7, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam256'],
            ['value' => 8, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam512'],
            ['value' => 9, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam1024'],
            ['value' => 10, 'generic' => 0, 'graph' => 1, 'descr' => 'Qam2048'],
            ]
        );

            $sensor_index = 4;
            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                '.1.3.6.1.4.1.33555.1.2.1.8.0',
                $sensor_index,
                $state_name,
                'Tx Modulation Status',
                1,
                1,
                null,
                null,
                null,
                null,
                $state,
                'snmp',
                0
            );

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $sensor_index);
    }
}
