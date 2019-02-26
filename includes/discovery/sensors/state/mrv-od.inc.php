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
        $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'lowAlarm'),
            array('value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'lowWarning'),
            array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'),
            array('value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'highWarning'),
            array('value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'highAlarm'),
        );
        create_state_index($state_name, $states);

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
                $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'lowAlarm'),
            array('value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'lowWarning'),
            array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'),
            array('value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'highWarning'),
            array('value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'highAlarm'),
        );
        create_state_index($state_name, $states);

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
        $states = array(
            array('value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'acBad'),
            array('value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'dcBad'),
            array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'acGood'),
            array('value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'dcGood'),
            array('value' => 6, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'good'),
            array('value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'),
        );
        create_state_index($state_name, $states);

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
        $states = array(
            array('value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'acBad'),
            array('value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'dcBad'),
            array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'acGood'),
            array('value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'dcGood'),
            array('value' => 6, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'good'),
            array('value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'),
        );
        create_state_index($state_name, $states);
        
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
        $states = array(
            array('value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'acBad'),
            array('value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'dcBad'),
            array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'acGood'),
            array('value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'dcGood'),
            array('value' => 6, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'good'),
            array('value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'),
        );
        create_state_index($state_name, $states);
        
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
        $states = array(
            array('value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'acBad'),
            array('value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'dcBad'),
            array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'acGood'),
            array('value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'dcGood'),
            array('value' => 6, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'good'),
            array('value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'),
        );
        create_state_index($state_name, $states);
        
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
        $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'),
            array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'good'),
            array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'),
        );
        create_state_index($state_name, $states);

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
        $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'),
            array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'good'),
            array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'),
        );
        create_state_index($state_name, $states);
        
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
        $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'),
            array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'good'),
            array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'),
        );
        create_state_index($state_name, $states);
        
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
        $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'),
            array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'good'),
            array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'),
        );
        create_state_index($state_name, $states);
        
        //Discover Sensor
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Fan 4', '1', '1', null, null, null, null, $oids['nbsCmmcChassisFan4Status'], 'snmp', 1);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
