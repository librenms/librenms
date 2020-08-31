<?php
/*
 * LibreNMS QuantaStor states module
 *
 * Copyright (c) 2020 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$tables = [
    ['hwDisk-State', '.1.3.6.1.4.1.39324.1.1.48.1.1.5.', '', 'QUANTASTOR-SYS-STATS', [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
    ]],
    ['hwEnclosure-PowerSupplyUnits', '.1.3.6.1.4.1.39324.1.1.50.1.1.21.', '', 'QUANTASTOR-SYS-STATS', [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
    ]],
    ['hwController', '.1.3.6.1.4.1.39324.1.1.51.1.1.5.', '', 'QUANTASTOR-SYS-STATS', [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
    ]],
    ['hwUnit', '.1.3.6.1.4.1.39324.1.1.49.1.1.5.', '', 'QUANTASTOR-SYS-STATS', [
        ['value' => 'OK', 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
    ]],
    ['storagePool', '.1.3.6.1.4.1.39324.1.1.7.1.1.5.', '', 'QUANTASTOR-SYS-STATS', [
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
            if ($num_oid === '.1.3.6.1.4.1.39324.1.1.48.1.1.5.') {
                $drive_bay = snmp_get($device, "hwDisk-Slot.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
                $drive_sn = snmp_get($device, "hwDisk-SerialNum.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
                $is_spare = snmp_get($device, "hwDisk-IsSpare.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
                if ($is_spare === "true") {
                    $spare = " [spare]";
                } else {
                    $spare = '';
                }
                $descr = "Disk " . $drive_bay . " SN: " . $drive_sn . $spare;
            }

            if ($num_oid === '.1.3.6.1.4.1.39324.1.1.50.1.1.21.') {
                $descr = 'PSU ' . $index;
            }

            if ($num_oid === '.1.3.6.1.4.1.39324.1.1.51.1.1.5.') {
                $ctrl = snmp_get($device, "hwController-Model.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
                $bbu = snmp_get($device, "hwController-HasBatteryBackupUnit.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
                if ($bbu === "true") {
                    $descr = $ctrl . ' w/ BBU';
                } else {
                    $descr = $ctrl . ' w/o BBU';
                }
            }

            if ($num_oid === '.1.3.6.1.4.1.39324.1.1.49.1.1.5.') {
                $volume = snmp_get($device, "hwUnit-Name.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
                $diskcount = snmp_get($device, "hwUnit-DiskList.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
                $descr = $volume . ' - ' . $diskcount . ' disk(s)';
            }

            if ($num_oid === '.1.3.6.1.4.1.39324.1.1.7.1.1.5.') {
                $poolname = snmp_get($device, "storagePool-Name.$index", '-Ovqn', 'QUANTASTOR-SYS-STATS');
                $descr = 'Pool ' . $poolname;
            }

            //Discover Sensors
            discover_sensor(
                $valid['sensor'],
                'state',
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
                $index
            );

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
