<?php
/*
 * LibreNMS NX-OS Fan state
 *
 * Copyright (c) 2016 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'nxos') {
    $fan_tray_oid = '.1.3.6.1.4.1.9.9.117.1.4.1.1.1';
    $fan_trays = snmpwalk_cache_oid_num($device, $fan_tray_oid, array());

    /* CISCO-ENTITY-FRU-CONTROL-MIB cefcFanTrayOperStatus
     *  unknown(1),
     *  up(2),
     *  down(3),
     *  warning(4)
     */

    if (is_array($fan_trays)) {
        foreach ($fan_trays as $oid => $array) {
            $state = current($array);
            $split_oid = explode('.', $oid);
            $index = $split_oid[(count($split_oid) - 1)];
            $current_oid = "$fan_tray_oid.$index";

            $entity_oid = '.1.3.6.1.2.1.47.1.1.1.1.7';
            $descr = trim(snmp_get($device, "$entity_oid.$index", '-Ovq'), '"');

            $state_name = "cefcFanTrayOperStatus";
            $state_index_id = create_state_index($state_name);

            if ($state_index_id !== null) {
                $states = array(
                    array($state_index_id, 'unknown', 0, 1, 3),
                    array($state_index_id, 'up',      1, 2, 0),
                    array($state_index_id, 'down',    1, 3, 2),
                    array($state_index_id, 'warning', 1, 4, 1),
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

            discover_sensor($valid['sensor'], 'state', $device, $current_oid, $index, $state_name, $descr, 1, 1);
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
