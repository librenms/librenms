<?php

/*
 * LibreNMS LNB Voltage State for the IRD PBI Headends
 *
 * © 2018 Jozef Rebjak <jozefrebjak@icloud.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('ird_state');
$ird_state_oid = '.1.3.6.1.4.1.1070.3.1.1.104.3.4.0';
$state_name = 'ird_state';
$descr = 'LNB Voltage';
$state = snmp_get($device, $ird_state_oid, '-Oqv');
if (!empty($state)) {
    $state_index_id = create_state_index($state_name);
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id, 'off', 0, 0, 3),
            array($state_index_id, '13V', 1, 1, 0),
            array($state_index_id, '18V', 1, 2, 1),
            array($state_index_id, 'failed', 1, 3, 2),
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
    discover_sensor($valid['sensor'], 'state', $device, $ird_state_oid, 1, $state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', 1);
    create_sensor_to_state_index($device, $state_name, 1);
}
