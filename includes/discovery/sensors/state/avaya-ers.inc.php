<?php
/**
 * avaya-ers.inc.php
 *
 * LibreNMS Fan and Power Supply state Discovery module for Avaya ERS
 */

if ($device['os'] === 'avaya-ers') {
    $oid = snmpwalk_cache_oid($device, 's5ChasComTable', array(), 'S5-CHASSIS-MIB');
    $cur_oid = '.1.3.6.1.4.1.45.1.6.3.3.1.1.10.';

    if (is_array($oid)) {
        //get states
        $state_name = 's5ChasComOperState';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id) {
            $states = array(
            array($state_index_id,'other'      ,0,1,3) ,
            array($state_index_id,'notAvail'   ,0,2,3) ,
            array($state_index_id,'removed'    ,0,3,3) ,
            array($state_index_id,'disabled'   ,0,4,3) ,
            array($state_index_id,'normal'     ,0,5,0) ,
            array($state_index_id,'resetInProg',0,6,1) ,
            array($state_index_id,'testing'    ,0,7,1) ,
            array($state_index_id,'warning'    ,0,8,1) ,
            array($state_index_id,'nonFatalErr',0,9,1) ,
            array($state_index_id,'fatalErr'   ,0,10,2) ,
            array($state_index_id,'notConfig'  ,0,11,3) ,
            array($state_index_id,'obsoleted'  ,0,12,3)
            );
        }
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
    
        // get fans (6) and temp (5) sensor only from walk
        $ers_sensors = array();
        foreach ($oid as $key => $value) {
            if ($key[s5ChasComGrpIndx] == 5 || $key[s5ChasComGrpIndx] == 6) {
                $ers_sensors[$key] = $value;
            }
        }

        foreach ($ers_sensors as $index => $entry) {
            //Get unit number
            $unit_array = explode(".", $index);
            $unit = floor($unit_array[1]/10);
            $descr = "Unit $unit: $entry[s5ChasComDescr]";
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, "s5ChasComOperState.$index", $state_name, $descr, '1', '1', null, null, null, null, $entry[s5ChasComOperState]);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, "s5ChasComOperState.$index");
        }
    }
}
