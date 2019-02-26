<?php
/**
 * boss.inc.php
 *
 * LibreNMS Fan and Power Supply state Discovery module for Extreme/Avaya ERS
 */

if ($device['os'] === 'boss') {
    $oid = snmpwalk_cache_oid($device, 's5ChasComTable', array(), 'S5-CHASSIS-MIB');
    $cur_oid = '.1.3.6.1.4.1.45.1.6.3.3.1.1.10.';

    if (is_array($oid)) {
        //get states
        $state_name = 's5ChasComOperState';
        $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'),
            array('value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'notAvail'),
            array('value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'removed'),
            array('value' => 4, 'generic' =>3, 'graph' => 0, 'descr' => 'disabled'),
            array('value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'),
            array('value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'resetInProg'),
            array('value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'testing'),
            array('value' => 8, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'),
            array('value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'nonFatalErr'),
            array('value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'fatalErr'),
            array('value' => 11, 'generic' => 3, 'graph' => 0, 'descr' => 'notConfig'),
            array('value' => 12, 'generic' => 3, 'graph' => 0, 'descr' => 'obsoleted'),
        );
        create_state_index($state_name, $states);

        // get fans (6) and temp (5) sensor only from walk
        $ers_sensors = array();
        foreach ($oid as $key => $value) {
            if ($key['s5ChasComGrpIndx'] == 5 || $key['s5ChasComGrpIndx'] == 6) {
                $ers_sensors[$key] = $value;
            }
        }

        foreach ($ers_sensors as $index => $entry) {
            //Get unit number
            $unit_array = explode(".", $index);
            $unit = floor($unit_array[1]/10);
            $descr = "Unit $unit: $entry[s5ChasComDescr]";
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, "s5ChasComOperState.$index", $state_name, $descr, '1', '1', null, null, null, null, $entry['s5ChasComOperState']);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, "s5ChasComOperState.$index");
        }
    }
}
