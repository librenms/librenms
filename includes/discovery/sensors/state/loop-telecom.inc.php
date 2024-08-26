<?php

$oids = snmpwalk_group($device, 'slotModelTable', 'L-AM3440-A-Private');
$current = $entry['sensorValue'];
if (! empty($oids)) {
    //Create State Index
    $state_name = 'ccCardState';
    $states = [
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'Empty'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Initializing'],
        ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'Working'],
        ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'Unplugged'],
        ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Failed'],
        ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'UnknownCard'],
        ['value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'BrandMismatch'],
        ['value' => 12, 'generic' => 1, 'graph' => 0, 'descr' => 'cardTypeMismatch'],
    ];
    //Model names corresponding to ccModelType
    $models = [
        'empty' => 1,
        'fe1' => 2,
        'ft1' => 3,
        'v35' => 4,
        'x50' => 5,
        'dtu-10' => 6,
        'mdsl' => 7,
        'em' => 8,
        'dtu-6' => 9,
        'router' => 10,
        'fxs' => 11,
        'fxo' => 12,
        'afr-e1' => 14,
        'afr-t1' => 15,
        'magneto' => 16,
        'quad-e1' => 21,
        'quad-t1' => 22,
        'v35-b' => 23,
        'mdsl-a' => 24,
        'v35-a' => 25,
        'x21-a' => 26,
        'v36-a' => 27,
        'g703' => 28,
        'mquad-e1' => 29,
        'mquad-t1' => 30,
        'gshdsl-2' => 31,
        'gshdsl-4' => 32,
        'rs422-a' => 33,
        'dry-contact' => 34,
        'fom' => 35,
        'router-a' => 36,
        'eia530-rs449-a' => 37,
        'ls-optical' => 38,
        'ocudp' => 39,
        'oct-rt-b' => 40,
        'ts' => 41,
        'rs232-8' => 42,
        'qfxo' => 43,
        'qfxs' => 44,
        'q2wem' => 45,
        'q4wem' => 46,
        'conference' => 47,
        'tri-e1' => 48,
        'tri-t1' => 49,
        'sdte' => 50,
        'tdmoe' => 51,
        'oct-dbra' => 52,
        'socudp' => 53,
        'octDte' => 54,
        'plm' => 55,
        'eca' => 56,
        'rs232a' => 57,
        'abra' => 58,
        'tta' => 59,
        'm4te' => 60,
        'dte6' => 61,
        'cda6' => 62,
        'qmag' => 63,
        'voip' => 64,
        'tri-rs232a' => 65,
        'clka' => 66,
        'other' => 98,
        'unknown' => 99,
        'ctrl' => 100,
    ];

    $test = create_state_index($state_name, $states);
    $num_oid = '.1.3.6.1.4.1.823.34441.1.9.1.9.';
    $num_index = 1; //Create a seperate index since $index = the slotname and not the number.
    foreach ($oids as $index => $entry) {
        //Discover Sensors
        $currentValue = $entry['ccCardState'];
        $modelint = $entry['ccModelType']; //Number representing card model
        $description = null;
        $modelName = array_search($modelint, $models);
        if (! is_null($modelName)) {
            $description = "$index ($modelName)"; //Set description equials to slot name with card type. Ex. Slot-A (mpls) or Slot-1 (FXS)
        } else {
            $description = $index; //Set description equials to slot name. Ex. Slot-A or Slot-1
        }

        discover_sensor($valid['sensor'], 'state', $device, $num_oid . $num_index, $index, $state_name, $description, '1', '1', null, null, null, null, $currentValue, 'snmp', null, null, null, 'Line cards');
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
        $num_index = $num_index + 1;
    }
}
