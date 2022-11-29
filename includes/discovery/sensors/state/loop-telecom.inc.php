<?php
$oids = snmpwalk_group($device, 'slotModelTable', 'L-AM3440-A-Private');
$current = $entry['sensorValue'];
if (!empty($oids)) {
    //Create State Index
    $state_name = 'ccCardState';
    $states = [
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'Empty'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Initializing'],
        ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'Working'],
        ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'Unplugged'],
        ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Failed'],
        ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'UnknownCard'],
        ['value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'BrandMismatch'],
        ['value' => 12, 'generic' => 1, 'graph' => 0, 'descr' => 'cardTypeMismatch']
    ];
    $test = create_state_index($state_name, $states);
    $num_oid = '.1.3.6.1.4.1.823.34441.1.9.1.9.';
    $num_index = 0; //Create a seperate index since $index = the slotname and not the number.
    foreach ($oids as $index => $entry) {
        //Discover Sensors
        $currentValue = $entry['ccCardState'];
        discover_sensor($valid['sensor'], 'state', $device, $num_oid . $num_index, $index, $state_name, $index, '1', '1', null, null, null, null, $currentValue, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
        $num_index = $num_index + 1;
    }
}
