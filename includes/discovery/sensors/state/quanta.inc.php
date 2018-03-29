<?php
/*
 * LibreNMS Quanta LB6M States information module
 *
 * Copyright (c) 2017 Mark Guzman <segfault@hasno.info>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('Quanta Chassis Power Supply state');

$tables = array(
    array('powerSupplyTable', 'boxServicesPowSupplyItemState', 'quantaPowerSupplyStatus', 'Power Supply ', 'NETGEAR-BOXSERVICES-PRIVATE-MIB', '.1.3.6.1.4.1.4413.1.1.43.1.7.1.3'),
);

foreach ($tables as $tablevalue) {
    $temp = snmpwalk_cache_multi_oid($device, $tablevalue[1], array(), $tablevalue[4]);
    $cur_oid = $tablevalue[1];

    if (is_array($temp)) {
        $state_name = $tablevalue[2];
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            if ($state_name == 'quantaPowerSupplyStatus') {
                $states = array(
                    array($state_index_id, 'other',        0, 0, 3),
                    array($state_index_id, 'notpresent',   0, 1, 3),
                    array($state_index_id, 'operational',  1, 2, 0),
                    array($state_index_id, 'failed',       0, 3, 2),
                    array($state_index_id, 'powering',     1, 4, 0),
                    array($state_index_id, 'nopower',      0, 5, 1),
                    array($state_index_id, 'notpowering',  0, 6, 1),
                    array($state_index_id, 'incompatible', 0, 7, 2),
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
        }

        foreach ($temp as $index => $entry) {
            $descr = $tablevalue[3] . $index;
            $oid_for_entry = $tablevalue[5] . '.' . $index;

            discover_sensor($valid['sensor'], 'state', $device, $oid_for_entry, $index, $state_name, $descr, '1', '1', null, null, null, null, $entry[$cur_oid], 'snmp');
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
