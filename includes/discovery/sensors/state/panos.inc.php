<?php

$temp = snmp_get($device, "panSysHAMode.0", "-Ovqe", "PAN-COMMON-MIB");
$cur_oid = '.1.3.6.1.4.1.25461.2.1.2.1.13.0';
$index = '0';

#Convert string to integer
if ($temp == 'active-passive') {
    $temp = 1;
} elseif ($temp == 'active-active') {
    $temp = 2;
}

if (is_numeric($temp)) {
    //Create State Index
    $state_name = 'panSysHAMode';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'Active-Passive',0,1,0) ,
            array($state_index_id,'Active-Active',0,2,0)
        );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],
                'state_generic_value' => $value[4]
            );
            dbInsert($insert, 'state_translations');
        }
    }

    $descr = 'High Availability Mode';
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, "panSysHAState.0", "-Ovqe", "PAN-COMMON-MIB");
$cur_oid = '.1.3.6.1.4.1.25461.2.1.2.1.11.0';
$index = '0';

#Convert string to integer
if ($temp == 'active') {
    $temp = 1;
} elseif ($temp == 'passive') {
    $temp = 2;
}

if (is_numeric($temp)) {
    //Create State Index
    $state_name = 'panSysHAState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'Active',0,1,0) ,
            array($state_index_id,'Passive',0,2,0)
        );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],
                'state_generic_value' => $value[4]
            );
            dbInsert($insert, 'state_translations');
        }
    }

    $descr = 'High Availability Local Status';
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, "panSysHAPeerState.0", "-Ovqe", "PAN-COMMON-MIB");
$cur_oid = '.1.3.6.1.4.1.25461.2.1.2.1.12.0';
$index = '0';

#Convert string to integer
if ($temp == 'active') {
    $temp = 1;
} elseif ($temp == 'passive') {
    $temp = 2;
}

if (is_numeric($temp)) {
    //Create State Index
    $state_name = 'panSysHAPeerState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'Active',0,1,0) ,
            array($state_index_id,'Passive',0,2,0)
        );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],
                'state_generic_value' => $value[4]
            );
            dbInsert($insert, 'state_translations');
        }
    }

    $descr = 'High Availability Peer Status';
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}
