<?php

echo 'AXIS States';

// Temp Sensor Status
$oids_tmp = snmpwalk_cache_multi_oid($device, 'tempSensorTable', array(), 'AXIS-VIDEO-MIB');
$cur_oid = '.1.3.6.1.4.1.368.4.1.3.1.3.1.';

// Exclude from $oids content .common string
foreach ($oids_tmp as $key_oids_tmp => $val_oids_tmp) {
    $oids[str_replace('common.', '', $key_oids_tmp)] = $val_oids_tmp;
}

if (is_array($oids)) {
    //Create State Index
    $state_name = 'tempSensorStatusState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Normal',0,1,0) ,
             array($state_index_id,'Failed',0,2,2) ,
             array($state_index_id,'Out Of Boundary',0,3,2) ,
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

    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, 'Temperature Sensor '.$index, '1', '1', null, null, null, null, $entry['tempSensorStatus'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

// Storage Status
$oids = snmpwalk_cache_multi_oid($device, 'storageTable', array(), 'AXIS-VIDEO-MIB');
$cur_oid = '.1.3.6.1.4.1.368.4.1.8.1.3.';

if (is_array($oids)) {
    //Create State Index
    $state_name = 'storageDisruptionDetectedState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Normal',0,1,0) ,
             array($state_index_id,'Failed',0,2,2) ,
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

    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, 'Storage Status: ' .$entry['storageName'], '1', '1', null, null, null, null, $entry['storageDisruptionDetected'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

unset($oids);
