<?php

echo 'Printer Status and Error State ';
$state = snmp_get($device, 'hrDeviceStatus.1', '-Ovqe', 'HOST-RESOURCES-MIB');
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'hrDeviceStatus';
    create_state_index(
        $state_name,
        [
            ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Running'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Warning'],
            ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'Testing'],
            ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Down'],
        ]
    );
    $sensor_index = 0;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.2.1.25.3.2.1.5.1',
        $sensor_index,
        $state_name,
        'Printer Device Status',
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

$state = snmp_get($device, 'hrPrinterDetectedErrorState.1', '-Ovqe', 'HOST-RESOURCES-MIB');
if ($state) {
    // https://www.ietf.org/rfc/rfc1759.txt hrPrinterDetectedErrorState
    //Create State Index
    $printer_states =
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Paper Low'],
            ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'No Paper'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Toner Low'],
            ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'No Toner'],
            ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Door Open'],
            ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'Jammed'],
            ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'Offline'],
            ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'Service Needed'],
            ['value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'Warning, multiple issues'],
            ['value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'Critical, multiple issues'],
        ];
    $bit_flags = q_bridge_bits2indices($state);
    $is_critical = false;
    if (count($bit_flags) == 0) {
        $state = 0;
    } else {
        for ($i = 0; $i < count($bit_flags); $i++) {
            // second octet of error flags not reliable, skipping
            if ($bit_flags[$i] > 8) {
                continue;
            }
            $state = $printer_states[$bit_flags[$i]]['value'];
            if ($printer_states[$bit_flags[$i]]['generic'] == 2) {
                $is_critical = true;
                break;
            }
        }
        // cannot create an index for each bit combination, instead warning or critical
        if (count($bit_flags) > 1) {
            $state = $is_critical ? 10 : 9;
        }
    }

    $state_name = 'hrPrinterDetectedErrorState';
    create_state_index($state_name, $printer_states);

    d_echo('Printer error state: ' . $state_name . ': ' . $state);
    $sensor_index = 0;
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.2.1.25.3.5.1.2.1',
        $sensor_index,
        $state_name,
        'Printer Error Status',
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
