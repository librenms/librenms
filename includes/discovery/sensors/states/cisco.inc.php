<?php

if ($device['os_group'] == 'cisco') {
    $temp = snmpwalk_cache_multi_oid($device, 'ciscoEnvMonSupplyStatusTable', array(), 'CISCO-ENVMON-MIB');
    $cur_oid = '.1.3.6.1.4.1.9.9.13.1.5.1.3.';

    if (is_array($temp)) {

        //Create State Index
        $state_name = 'ciscoEnvMonSupplyState';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'normal',0,1,0) ,
                 array($state_index_id,'warning',0,2,1) ,
                 array($state_index_id,'critical',0,3,2) ,
                 array($state_index_id,'shutdown',0,4,3) ,
                 array($state_index_id,'notPresent',0,5,3) ,
                 array($state_index_id,'notFunctioning',0,6,2)
             );
            foreach($states as $value){ 
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

        foreach ($temp as $index => $entry) {
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $temp[$index]['ciscoEnvMonSupplyStatusDescr'], '1', '1', null, null, null, null, $temp[$index]['ciscoEnvMonSupplyState'], 'snmp', $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
