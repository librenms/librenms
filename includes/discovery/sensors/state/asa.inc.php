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

$temp = snmpwalk_cache_multi_oid($device, 'cfwHardwareStatusTable', [], 'CISCO-FIREWALL-MIB');
$cur_oid = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.3.';

if (is_array($temp)) {
    //Create State Index
    if (strstr($temp['netInterface']['cfwHardwareStatusDetail'], 'not Configured') == false) {
        $state_name = 'cfwHardwareStatus';
        $states = [
            ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'other'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
            ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'down'],
            ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'error'],
            ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'overTemp'],
            ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'busy'],
            ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'noMedia'],
            ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'backup'],
            ['value' => 9, 'generic' => 0, 'graph' => 0, 'descr' => 'active'],
            ['value' => 10, 'generic' => 0, 'graph' => 0, 'descr' => 'standby'],
        ];
        create_state_index($state_name, $states);

        foreach ($temp as $index => $entry) {
            $descr = ucwords(trim(preg_replace('/\s*\([^\s)]*\)/', '', $temp[$index]['cfwHardwareInformation'])));
            if ($index == 'netInterface') {
                $index = 4;
            } elseif ($index == 'primaryUnit') {
                $index = 6;
            } elseif ($index == 'secondaryUnit') {
                $index = 7;
            }
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, $index, $state_name, $descr, 1, 1, null, null, null, null, $temp[$index][' cfwHardwareStatusValue'], 'snmp', $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
