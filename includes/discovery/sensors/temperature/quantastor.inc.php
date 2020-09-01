<?php
/*
 * LibreNMS QuantaStor temperature module
 *
 * Copyright (c) 2020 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$tables = [
    ['hwDisk-DriveTemp', '.1.3.6.1.4.1.39324.1.1.48.1.1.30.', '', 'QUANTASTOR-SYS-STATS', [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
    ]],
];

foreach ($tables as $tablevalue) {
    list($oid, $num_oid, $descr, $mib, $states) = $tablevalue;
    $temp = snmpwalk_cache_multi_oid($device, $oid, [], $mib, null, '-OQUse');
    if (!empty($temp)) {
        //Create State Index
        $state_name = $oid;
        $state_index_id = create_state_index($state_name, $states);

        foreach ($temp as $index => $entry) {
            $sensor_type = 'temperature';
            $descr = 'Disk ' . snmp_get($device, "hwDisk-Slot.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
            preg_match('/([0-9]+.)/', $index, $value);
            //Discover Sensors
            discover_sensor(
                $valid['sensor'],
                $sensor_type,
                $device,
                $num_oid . $index,
                $index,
                $state_name,
                $descr,
                1,
                1,
                null,
                null,
                null,
                null,
                $entry[$oid],
                'snmp',
                $value[0]
            );

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
