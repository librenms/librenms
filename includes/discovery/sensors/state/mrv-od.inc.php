<?php

/*
 * Sensor State discovery module for the MRV® OptiDriver® Optical Transport Platform
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo "MRV OptiDriver";

foreach ($pre_cache['mrv-od_port-table'] as $index => $entry) {
    // RX Interface Power State
    if ($entry['nbsCmmcPortRxPowerLevel']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.8.1.1.66.';
        //Create State Index
        $state_name = 'nbsCmmcPortRxPowerLevel';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notSupported',0,1,3) ,
                 array($state_index_id,'lowAlarm',0,2,2) ,
                 array($state_index_id,'lowWarning',0,3,1) ,
                 array($state_index_id,'ok',0,4,0) ,
                 array($state_index_id,'highWarning',0,5,1) ,
                 array($state_index_id,'highAlarm',0,6,2)
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
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Rx Power State';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $entry['nbsCmmcPortRxPowerLevel'], 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // TX Interface Power State
    if ($entry['nbsCmmcPortTxPowerLevel']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.8.1.1.65.';
        //Create State Index
        $state_name = 'nbsCmmcPortTxPowerLevel';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notSupported',0,1,3) ,
                 array($state_index_id,'lowAlarm',0,2,2) ,
                 array($state_index_id,'lowWarning',0,3,1) ,
                 array($state_index_id,'ok',0,4,0) ,
                 array($state_index_id,'highWarning',0,5,1) ,
                 array($state_index_id,'highAlarm',0,6,2)
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
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Tx Power State';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $entry['nbsCmmcPortTxPowerLevel'], 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

foreach ($pre_cache['mrv-od_chassis-entry'] as $index => $entry) {
    // Power Supply 1
    if ($entry['nbsCmmcChassisPS1Status']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.6.1.1.7.1';
        //Create State Index
        $state_name = 'nbsCmmcChassisPS1Status';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notInstalled',0,1,1) ,
                 array($state_index_id,'acBad',0,2,2) ,
                 array($state_index_id,'dcBad',0,3,2) ,
                 array($state_index_id,'acGood',0,4,0) ,
                 array($state_index_id,'dcGood',0,5,0) ,
                 array($state_index_id,'notSupported',0,6,3) ,
                 array($state_index_id,'good',0,7,0) ,
                 array($state_index_id,'bad',0,8,2)
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
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Power Supply 1', '1', '1', null, null, null, null, $oids['nbsCmmcChassisPS1Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // Power Supply 2
    if ($entry['nbsCmmcChassisPS2Status']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.6.1.1.8.1';
        //Create State Index
        $state_name = 'nbsCmmcChassisPS2Status';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notInstalled',0,1,1) ,
                 array($state_index_id,'acBad',0,2,2) ,
                 array($state_index_id,'dcBad',0,3,2) ,
                 array($state_index_id,'acGood',0,4,0) ,
                 array($state_index_id,'dcGood',0,5,0) ,
                 array($state_index_id,'notSupported',0,6,3) ,
                 array($state_index_id,'good',0,7,0) ,
                 array($state_index_id,'bad',0,8,2)
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
        //Discover Sensor
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Power Supply 2', '1', '1', null, null, null, null, $oids['nbsCmmcChassisPS2Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // Power Supply 3
    if ($entry['nbsCmmcChassisPS3tatus']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.6.1.1.9.1';
        //Create State Index
        $state_name = 'nbsCmmcChassisPS3Status';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notInstalled',0,1,1) ,
                 array($state_index_id,'acBad',0,2,2) ,
                 array($state_index_id,'dcBad',0,3,2) ,
                 array($state_index_id,'acGood',0,4,0) ,
                 array($state_index_id,'dcGood',0,5,0) ,
                 array($state_index_id,'notSupported',0,6,3) ,
                 array($state_index_id,'good',0,7,0) ,
                 array($state_index_id,'bad',0,8,2)
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
        //Discover Sensor
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Power Supply 3', '1', '1', null, null, null, null, $oids['nbsCmmcChassisPS3Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // Power Supply 4
    if ($entry['nbsCmmcChassisPS4Status']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.6.1.1.10.1';
        //Create State Index
        $state_name = 'nbsCmmcChassisPS4Status';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notInstalled',0,1,1) ,
                 array($state_index_id,'acBad',0,2,2) ,
                 array($state_index_id,'dcBad',0,3,2) ,
                 array($state_index_id,'acGood',0,4,0) ,
                 array($state_index_id,'dcGood',0,5,0) ,
                 array($state_index_id,'notSupported',0,6,3) ,
                 array($state_index_id,'good',0,7,0) ,
                 array($state_index_id,'bad',0,8,2)
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
        //Discover Sensor
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Power Supply 4', '1', '1', null, null, null, null, $oids['nbsCmmcChassisPS4Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // Power Supply 4
    if ($entry['nbsCmmcChassisFan1Status']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.6.1.1.11.1';
        //Create State Index
        $state_name = 'nbsCmmcChassisFan1Status';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notSupported',0,1,3) ,
                 array($state_index_id,'bad',0,2,2) ,
                 array($state_index_id,'good',0,3,0) ,
                 array($state_index_id,'notInstalled',0,4,1)
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
        //Discover Sensor
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Fan 1', '1', '1', null, null, null, null, $oids['nbsCmmcChassisFan1Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // Fan 2
    if ($entry['nbsCmmcChassisFan2Status']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.6.1.1.12.1';
        //Create State Index
        $state_name = 'nbsCmmcChassisFan2Status';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notSupported',0,1,3) ,
                 array($state_index_id,'bad',0,2,2) ,
                 array($state_index_id,'good',0,3,0) ,
                 array($state_index_id,'notInstalled',0,4,1)
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
        //Discover Sensor
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Fan 2', '1', '1', null, null, null, null, $oids['nbsCmmcChassisFan2Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // Fan 3
    if ($entry['nbsCmmcChassisFan3Status']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.6.1.1.13.1';
        //Create State Index
        $state_name = 'nbsCmmcChassisFan3Status';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notSupported',0,1,3) ,
                 array($state_index_id,'bad',0,2,2) ,
                 array($state_index_id,'good',0,3,0) ,
                 array($state_index_id,'notInstalled',0,4,1)
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
        //Discover Sensor
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Fan 3', '1', '1', null, null, null, null, $oids['nbsCmmcChassisFan3Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // Fan 4
    if ($entry['nbsCmmcChassisFan4Status']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.6.1.1.14.1';
        //Create State Index
        $state_name = 'nbsCmmcChassisFan4Status';
        $state_index_id = create_state_index($state_name);
        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'notSupported',0,1,3) ,
                 array($state_index_id,'bad',0,2,2) ,
                 array($state_index_id,'good',0,3,0) ,
                 array($state_index_id,'notInstalled',0,4,1)
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
        //Discover Sensor
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Fan 4', '1', '1', null, null, null, null, $oids['nbsCmmcChassisFan4Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
