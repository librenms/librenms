<?php

$state = snmp_get($device, "upsBatteryStatus.0", "-Ovqe", "UPS-MIB");
$cur_oid = '.1.3.6.1.2.1.33.1.2.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'upsBatteryStatus';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'unknown',0,1,3) ,
            array($state_index_id,'batteryNormal',0,2,0) ,
            array($state_index_id,'batteryLow',0,3,2) ,
            array($state_index_id,'batteryDepleted',0,4,2) ,
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

    $descr = 'Battery Status';
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

$state = snmp_get($device, "upsOutputSource.0", "-Ovqe", "UPS-MIB");
$cur_oid = '.1.3.6.1.2.1.33.1.4.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'upsOutputSource';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'other',0,1,3) ,
            array($state_index_id,'none',0,2,3) ,
            array($state_index_id,'normal',0,3,0) ,
            array($state_index_id,'bypass',0,4,2) ,
            array($state_index_id,'battery',0,5,1) ,
            array($state_index_id,'booster',0,6,1) ,
            array($state_index_id,'reducer',0,7,1) ,
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

    $descr = 'Output Source Status';
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}