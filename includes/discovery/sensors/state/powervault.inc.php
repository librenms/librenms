<?php
/*
 * LibreNMS Powervault state
 *
 * Copyright (c) 2017 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$state = snmp_get($device, "DELL-SHADOW-MIB::shadowStatusGlobalStatus.0", '-Oqne');
list($oid, $value) = explode(' ', $state);

if (is_numeric($value)) {
    $descr = "Global Status";
    $state_name = "shadowStatusGlobalStatus";
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id, 'other',           0, 1, 2),
            array($state_index_id, 'unknown',         1, 2, 3),
            array($state_index_id, 'ok',              1, 3, 0),
            array($state_index_id, 'critical',        1, 4, 2),
            array($state_index_id, 'non-Recoverable', 1, 5, 2),
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

    discover_sensor($valid['sensor'], 'state', $device, $oid, 1, $state_name, $descr, 1, 1);
    create_sensor_to_state_index($device, $state_name, 1);
}
