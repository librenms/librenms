<?php
$oids = snmpwalk_cache_numerical_oid($device, 'tmnxChassisPowerSupplyTable', $power_supply_table = array(), 'TIMETRA-CHASSIS-MIB', null, '-OQUsne');

if (!empty($oids)) {
    // Create State Index
    $dev_state_name = 'tmnxDeviceState';
    $dev_states = array(
        array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'),
        array('value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'Not Equipped'),
        array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'),
        array('value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'Failed'),
        array('value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'Out of Service')
    );

    create_state_index($dev_state_name, $dev_states);

    $ps_state_name = 'tmnxChassisPowerSupplyAssignedTypes';
    $ps_states = array(
        array('value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'None'),
        array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'DC'),
        array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Single AC'),
        array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'Multiple AC')
    );

    create_state_index($ps_state_name, $ps_states);
    
    $ps_table = array(
        array('tbl_index' => 2, 'desc' => 'AC Status PS ', 'type' => $dev_state_name),
        array('tbl_index' => 3, 'desc' => 'DC Status PS ', 'type' => $dev_state_name),
        array('tbl_index' => 4, 'desc' => 'Temperature Status PS ', 'type' => $dev_state_name),
        array('tbl_index' => 6, 'desc' => 'Power Supply 1 Status PS ', 'type' => $dev_state_name),
        array('tbl_index' => 7, 'desc' => 'Power Supply 2 Status PS ', 'type' => $dev_state_name),
        array('tbl_index' => 8, 'desc' => 'Assigned Type PS ', 'type' => $ps_state_name),
        array('tbl_index' => 9, 'desc' => 'Input Status PS ', 'type' => $dev_state_name),
        array('tbl_index' => 10, 'desc' => 'Output Status PS ', 'type' => $dev_state_name),
    );

    $base_oid = '.1.3.6.1.4.1.6527.3.1.2.2.1.5.1.';
    
    foreach ($ps_table as $entry) {
        foreach ($oids as $index => $value) {
            $num_oid = $base_oid.$entry['tbl_index'].".1.".$index;
            $sensor_index = 'tmnxPowerSupplyTable.'.$entry['tbl_index'].".1.".$index;
            $desc = $entry['desc'].$index;

            //Discover sensors
            discover_sensor($valid['sensor'], 'state', $device, $num_oid, $sensor_index, $entry['type'], $desc, 1, 1, null, null, null, null, $value[$num_oid]);

            // Create sensor to state index
            create_sensor_to_state_index($device, $entry['type'], $sensor_index);
        }
    }
}
