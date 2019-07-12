## Created 05/07/19 - Sam R 

<?php
echo 'Scality RING Status';
$state = snmp_get($device, "ringState.1", "-Ovqe", 'SCALITY-MIB');
if (is_string($state)) {    
    //Create State Index    
    $state_name = 'ringState';    
    create_state_index(
            $state_name,
                [
                    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'RUN'],
                    ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'BALANCING'],
                    ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'LOOP'],
                ]    
        );
    $sensor_index = 0;    
    discover_sensor(
        $valid['sensor'],
            'state',
            $device,
            '.1.3.6.1.4.1.37489.2.1.1.1.4.1.1.8.1',
            $sensor_index,
            $state_name,
            'Scality RING Status',
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
