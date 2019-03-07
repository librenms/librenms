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
    // Common Rx/Tx States
    $states = [
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'lowAlarm'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'lowWarning'],
        ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'highWarning'],
        ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'highAlarm'],
    ];

    // RX Interface Power State
    if ($entry['nbsCmmcPortRxPowerLevel']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.8.1.1.66.';
        //Create State Index
        $state_name = 'nbsCmmcPortRxPowerLevel';
        create_state_index($state_name, $states);

        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Rx Power State';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, 1, 1, null, null, null, null, $entry['nbsCmmcPortRxPowerLevel'], 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
    // TX Interface Power State
    if ($entry['nbsCmmcPortTxPowerLevel']) {
        $cur_oid = '.1.3.6.1.4.1.629.200.8.1.1.65.';
        //Create State Index
        $state_name = 'nbsCmmcPortTxPowerLevel';
        create_state_index($state_name, $states);

        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Tx Power State';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, 1, 1, null, null, null, null, $entry['nbsCmmcPortTxPowerLevel'], 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

foreach ($pre_cache['mrv-od_chassis-entry'] as $index => $entry) {
    // Common Power Supply States
    $states = [
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'acBad'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'dcBad'],
        ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'acGood'],
        ['value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'dcGood'],
        ['value' => 6, 'generic' => 3, 'graph' => 0, 'descr' => 'notSupported'],
        ['value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'good'],
        ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'bad'],
    ];

    $powerSupplies = [
        ['entity' => 'nbsCmmcChassisPS1Status', 'num_oid' => '.1.3.6.1.4.1.629.200.6.1.1.7.1', 'descr' => 'Power Supply 1'],
        ['entity' => 'nbsCmmcChassisPS2Status', 'num_oid' => '.1.3.6.1.4.1.629.200.6.1.1.8.1', 'descr' => 'Power Supply 2'],
        ['entity' => 'nbsCmmcChassisPS3Status', 'num_oid' => '.1.3.6.1.4.1.629.200.6.1.1.9.1', 'descr' => 'Power Supply 3'],
        ['entity' => 'nbsCmmcChassisPS4Status', 'num_oid' => '.1.3.6.1.4.1.629.200.6.1.1.10.1', 'descr' => 'Power Supply 4'],
    ];

    foreach ($powerSupplies as $psu) {
        if ($entry[$psu['entity']]) {
            //Create State Index
            $state_name = $psu['entity'];
            create_state_index($state_name, $states);

            $cur_oid    = $psu['num_oid'];
            $descr      = $psu['descr'];

            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $oids[$state_name], 'snmp', 1);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }

    $fans = [
        ['entity' => 'nbsCmmcChassisFan1Status', 'num_oid' => '.1.3.6.1.4.1.629.200.6.1.1.11.1', 'descr' => 'Fan 1'],
        ['entity' => 'nbsCmmcChassisFan2Status', 'num_oid' => '.1.3.6.1.4.1.629.200.6.1.1.12.1', 'descr' => 'Fan 2'],
        ['entity' => 'nbsCmmcChassisFan3Status', 'num_oid' => '.1.3.6.1.4.1.629.200.6.1.1.13.1', 'descr' => 'Fan 3'],
        ['entity' => 'nbsCmmcChassisFan4Status', 'num_oid' => '.1.3.6.1.4.1.629.200.6.1.1.14.1', 'descr' => 'Fan 4'],
    ];

    foreach ($fans as $fan) {
        if ($entry[$psu['entity']]) {
            //Create State Index
            $state_name = $fan['entity'];
            create_state_index($state_name, $states);

            $cur_oid    = $fan['num_oid'];
            $descr      = $fan['descr'];

            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $oids[$state_name], 'snmp', 1);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
