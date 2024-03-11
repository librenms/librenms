<?php

$oids = snmpwalk_group($device, 'PowerSupplyEntry', 'CHECKPOINT-MIB');

if (! empty($oids)) {
    //Create State Index
    $state_name = 'CheckpointPowerSupplyStatus';

    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 2, 'descr' => 'Up'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Down'],
    ];

    create_state_index($state_name, $states);

    $num_oid = '1.3.6.1.4.1.2620.1.6.7.9.1.1.2.';
    foreach ($oids as $index => $entry) {
        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            '.' . $num_oid . $index . '.0',
            $index,
            $state_name,
            'PowerSupply #' . $entry['powerSupplyIndex'][0],
            '1',
            '1',
            null,
            null,
            null,
            null,
            $entry['powerSupplyStatus'][0],
            'snmp',
            $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
