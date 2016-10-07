<?php
/*
 * LibreNMS QNAP NAS HDD SMART/Status information module
 *
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'qnap') {
    echo 'QNAP: ';

    $disks_oid = '.1.3.6.1.4.1.24681.1.2.10.0';
    $total_disks = snmp_get($device, $disks_oid, '-Oqv');

    $status_oid = '.1.3.6.1.4.1.24681.1.2.11.1.4.';
    $smart_oid = '.1.3.6.1.4.1.24681.1.2.11.1.7.';

    d_echo("QNAP HDD status\n");
    for ($i = 1; $i <= $total_disks; $i++) {
        $state = snmp_get($device, $status_oid . $i, '-Oqv');
        $status_name = 'qnap_hdd_status_' . $i;
        $status_descr = 'HDD ' . $i . ' status';
        if (is_numeric($state)) {
            $state_index_id = create_state_index($status_name);

            if ($state_index_id !== null) {
                $states = array(
                    array($state_index_id, 'ready', 1, 0, 0),
                    array($state_index_id, 'unknown', 0, -4, 1),
                    array($state_index_id, 'no disk', 0, -5, 1),
                    array($state_index_id, 'invalid', 0, -6, 1),
                    array($state_index_id, 'rw Error', 2, -9, 2),
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
            discover_sensor($valid['sensor'], 'state', $device, $status_oid . $i, 1, $status_name, $status_descr, '1', '1', null, null, null, null, $state, 'snmp', 1);
            create_sensor_to_state_index($device, $status_name, 1);
        }
    }

    d_echo("QNAP HDD SMART\n");
    for ($i = 1; $i <= $total_disks; $i++) {
        $state = str_replace('"', '', snmp_get($device, $smart_oid . $i, '-Oqv'));
        $smart_name = 'qnap_hdd_smart_' . $i;
        $smart_descr = 'HDD ' . $i . ' SMART';

        switch ($state) {
            case 'Normal':
                $state = 1;
                break;
            case '--':
                $state = 0;
                break;
            case 'Warning':
                $state = 2;
                break;
        }

        if (is_numeric($state)) {
            $state_index_id = create_state_index($smart_name);

            if ($state_index_id !== null) {
                $states = array(
                    array($state_index_id, 'normal', 1, 1, 0),
                    array($state_index_id, 'no disk', 0, 0, 1),
                    array($state_index_id, 'warning', 2, 2, 2),
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
            discover_sensor($valid['sensor'], 'state', $device, $smart_oid . $i, 1, $smart_name, $smart_descr, '1', '1', null, null, null, null, $state, 'snmp', 1);
            create_sensor_to_state_index($device, $smart_name, 1);
        }
    }
}
