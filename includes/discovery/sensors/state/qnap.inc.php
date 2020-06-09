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
        $states = [
            ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'ready'],
            ['value' => -4, 'generic' => 1, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => -5, 'generic' => 1, 'graph' => 0, 'descr' => 'no disk'],
            ['value' => -6, 'generic' => 1, 'graph' => 0, 'descr' => 'invalid'],
            ['value' => -9, 'generic' => 2, 'graph' => 2, 'descr' => 'rw Error'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $status_oid . $i, 1, $status_name, $status_descr, 1, 1, null, null, null, null, $state, 'snmp', 1);
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
        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'normal'],
            ['value' => 0, 'generic' => 1, 'graph' => 0, 'descr' => 'no disk'],
            ['value' => 2, 'generic' => 2, 'graph' => 2, 'descr' => 'warning'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $smart_oid . $i, 1, $smart_name, $smart_descr, 1, 1, null, null, null, null, $state, 'snmp', 1);
        create_sensor_to_state_index($device, $smart_name, 1);
    }
}
