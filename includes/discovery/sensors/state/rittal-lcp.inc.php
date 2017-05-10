<?php


// Unit Status (Value : 1 ok, 2 failed, 3 overload)
$state = snmp_get($device, "cmcIIIUnitStatus.0", "-Ovqe", 'RITTAL-CMC-III-MIB');
$cur_oid = '.1.3.6.1.4.1.2606.7.2.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'cmcIIIUnitStatus';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
            array($state_index_id,'OK',0,1,0) ,
            array($state_index_id,'Failed',0,2,2) ,
            array($state_index_id,'Overload',0,3,1) ,
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
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Unit Status', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}
