<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$temp = snmpwalk_cache_multi_oid($device, 'snChasPwrSupplyTable', [], 'FOUNDRY-SN-AGENT-MIB');
$cur_oid = '.1.3.6.1.4.1.1991.1.1.1.2.1.1.3.';

if (is_array($temp)) {
    //Create State Index
    $state_name = 'snChasPwrSupplyOperStatus';
    $states = [
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'failure'],
    ];
    create_state_index($state_name, $states);

    foreach ($temp as $index => $entry) {
        //Discover Sensors
        $descr = $temp[$index]['snChasPwrSupplyDescription'];
        if (empty($descr)) {
            $descr = "Power Supply " . $index;
        }
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, 1, 1, null, null, null, null, $temp[$index]['snChasPwrSupplyOperStatus'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
