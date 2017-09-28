<?php
/*
 * LibreNMS Ubiquiti EdgeSwitch States information module
 *
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('UBNT EdgeSwitch Chassis state');
//EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempUnitState
$chassis_state_oid = '.1.3.6.1.4.1.4413.1.1.43.1.15.1.2.1';
$state_name = 'edgeswitch_state';
$descr = 'Chassis state';
$state = snmp_get($device, $chassis_state_oid, '-Oqv');
if (!empty($state)) {
    $state_index_id = create_state_index($state_name);
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id, 'other', 0, 0, 3),
            array($state_index_id, 'ok', 1, 1, 0),
            array($state_index_id, 'degraded', 1, 2, 1),
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
    discover_sensor($valid['sensor'], 'state', $device, $chassis_state_oid, 1, $state_name, $descr, '1', '1', null, null, null, null, $state, 'snmp', 1);
    create_sensor_to_state_index($device, $state_name, 1);
}
