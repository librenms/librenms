<?php

echo 'RFC1628 ';

// Battery Status (Value : 1 unknown, 2 batteryNormal, 3 batteryLow, 4 batteryDepleted)
$state = snmp_get($device, "upsBatteryStatus.0", "-Ovqe", 'UPS-MIB');
$cur_oid = '.1.3.6.1.2.1.33.1.2.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'upsBatteryStatusState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
            array($state_index_id,'Unknown',0,1,3) ,
            array($state_index_id,'Normal',0,2,0) ,
            array($state_index_id,'Low',0,3,2) ,
            array($state_index_id,'Depleted',0,4,2) ,
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

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Battery Status', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

// Output Source (Value : 1 other, 2 none, 3 normal, 4 bypass, 5 battery, 6 booster, 7 reducer)
$state = snmp_get($device, "upsOutputSource.0", "-Ovqe", 'UPS-MIB');
$cur_oid = '.1.3.6.1.2.1.33.1.4.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'upsOutputSourceState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
            array($state_index_id,'Other',0,1,3) ,
            array($state_index_id,'None',0,2,3) ,
            array($state_index_id,'Normal',0,3,0) ,
            array($state_index_id,'Bypass',0,4,2) ,
            array($state_index_id,'Battery',0,5,2) ,
            array($state_index_id,'Booster',0,6,2) ,
            array($state_index_id,'Reducer',0,7,2) ,
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

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Output Source', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}
