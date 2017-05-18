<?php

$nokia_device_state_name  = 'tmnxDeviceState';
$nokia_device_state_index = create_state_index($nokia_device_state_name);
if ($nokia_device_state_index !== null) {
    $nokia_device_states      = array(
        array($nokia_device_state_index, 'Unknown', 1,1,3),
        array($nokia_device_state_index, 'Not Equippped', 1,2,3),
        array($nokia_device_state_index, 'Ok', 1,3,0),
        array($nokia_device_state_index, 'Failed', 1,4,2),
        array($nokia_device_state_index, 'Out Of Service', 1,5,1),
        array($nokia_device_state_index, 'Not Provisioned', 1,6,3),
    );
    foreach ($nokia_device_states as $state) {
        $insert = array(
            'state_index_id'        => $state[0],
            'state_descr'           => $state[1],
            'state_draw_graph'      => $state[2],
            'state_value'           => $state[3],
            'state_generic_value'   => $state[4],
        );
        dbInsert($insert, 'state_translations');
    }
}

$nokia_device_ps_assigned_types_name  = 'tmnxChassisPowerSupplyAssignedTypes';
$nokia_device_ps_assigned_types_index = create_state_index($nokia_device_ps_assigned_types_name);
if ($nokia_device_ps_assigned_types_index !== null) {
    $nokia_device_ps_assigned_types      = array(
        array($nokia_device_ps_assigned_types_index, 'None', 0,0,0),
        array($nokia_device_ps_assigned_types_index, 'DC', 0,1,0),
        array($nokia_device_ps_assigned_types_index, 'Single AC', 0,2,0),
        array($nokia_device_ps_assigned_types_index, 'Multiple AC', 0,3,0),
        array($nokia_device_ps_assigned_types_index, 'default', 0,4,3),
    );
    foreach ($nokia_device_ps_assigned_types as $state) {
        $insert = array(
          'state_index_id'        => $state[0],
          'state_descr'           => $state[1],
          'state_draw_graph'      => $state[2],
          'state_value'           => $state[3],
          'state_generic_value'   => $state[4],
        );
        dbInsert($insert, 'state_translations');
    }
}

$base_oid          = '.1.3.6.1.4.1.6527.3.1.2.2.1.5.1.';
$power_supply_oids = array(
    array('sub_oid' => '2.1.1', 'desc' => 'AC Status', 'type' => $nokia_device_state_name),
    array('sub_oid' => '3.1.1', 'desc' => 'DC Status', 'type' => $nokia_device_state_name),
    array('sub_oid' => '6.1.1', 'desc' => 'Power Supply 1', 'type' => $nokia_device_state_name),
    array('sub_oid' => '7.1.1', 'desc' => 'Power Supply 2', 'type' => $nokia_device_state_name),
    array('sub_oid' => '8.1.1', 'desc' => 'Power Supply Type', 'type' => $nokia_device_ps_assigned_types_name),
    array('sub_oid' => '9.1.1', 'desc' => 'Power In', 'type' => $nokia_device_state_name),
    array('sub_oid' => '10.1.1', 'desc' => 'Power Out', 'type' => $nokia_device_state_name),
);


$pst = snmpwalk_cache_numerical_oid($device, 'tmnxChassisPowerSupplyTable', $power_supply_table = array(), 'TIMETRA-CHASSIS-MIB', 'aos', '-OQUsn');
$power_supply_table = end($pst);

foreach ($power_supply_oids as $data) {
    $full_oid = $base_oid . $data['sub_oid'];
    $index = "tmnxChassisPowerSupplyTable." . $data['sub_oid'];
    discover_sensor($valid['sensor'], 'state', $device, $full_oid, $index, $data['type'], $data['desc'], 1, 1, null, null, null, null, $power_supply_table[$full_oid], 'snmp', $index);
    create_sensor_to_state_index($device, $data['type'], $index);
    unset($full_oid);
}

unset(
    $power_supply_state_table,
    $nokia_device_state_name,
    $nokia_device_state_index,
    $nokia_device_states,
    $nokia_device_ps_assigned_types_name,
    $nokia_device_ps_assigned_types_index,
    $nokia_device_ps_assigned_types,
    $base_oid,
    $power_supply_oids
);
