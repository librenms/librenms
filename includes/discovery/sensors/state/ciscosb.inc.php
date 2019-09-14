<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$temp = snmpwalk_cache_multi_oid($device, 'swIfOperSuspendedStatus', [], 'CISCOSB-rlInterfaces');
$cur_oid = '.1.3.6.1.4.1.9.6.1.101.43.1.1.24.';

if (is_array($temp)) {
    //Create State Index
    $state_name = 'swIfOperSuspendedStatus';
    $states = [
        ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'true'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'false'],
    ];
    create_state_index($state_name, $states);

    foreach ($temp as $index => $entry) {
        $port_data = get_port_by_index_cache($device['device_id'], str_replace('1.', '', $index));
        $descr = $port_data['ifDescr'] . ' Suspended Status';
        if (str_contains($descr, ['ethernet','Ethernet']) && $port_data['ifOperStatus'] !== 'notPresent') {
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, $index, $state_name, $descr, 1, 1, null, null, null, null, $temp[$index]['swIfOperSuspendedStatus'], 'snmp', $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
