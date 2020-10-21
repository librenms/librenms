<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$tables = [
    ['virtualDiskTable', '.1.3.6.1.4.1.674.10892.5.5.1.20.140.1.1.4.', 'virtualDiskState', 'virtualDiskName'],
    ['processorDeviceTable', '.1.3.6.1.4.1.674.10892.5.4.1100.30.1.5.', 'processorDeviceStatus', 'processorDeviceBrandName'],
    ['memoryDeviceTable', '.1.3.6.1.4.1.674.10892.5.4.1100.50.1.5.', 'memoryDeviceStatus', 'memoryDeviceLocationName'],
    ['voltageProbeTable', '.1.3.6.1.4.1.674.10892.5.4.600.20.1.5.', 'voltageProbeStatus', 'voltageProbeLocationName'],
    ['amperageProbeTable', '.1.3.6.1.4.1.674.10892.5.4.600.30.1.5.', 'amperageProbeStatus', 'amperageProbeLocationName'],
    ['systemBatteryTable', '.1.3.6.1.4.1.674.10892.5.4.600.50.1.5.', 'systemBatteryStatus', 'systemBatteryLocationName'],
];

foreach ($tables as $tablevalue) {
    [$table_oid, $num_oid, $value_oid, $descr_oid] = $tablevalue;
    $temp = snmpwalk_cache_multi_oid($device, $table_oid, [], 'IDRAC-MIB-SMIv2', null, '-OQUse');
    // '-OQUsetX'

    if (! empty($temp)) {
        // Find the right states
        if ($value_oid == 'virtualDiskState') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'online'],
                ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'degraded'],
            ];
        } elseif ($value_oid == 'processorDeviceStatus' || $value_oid == 'memoryDeviceStatus') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
                ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'nonCritical'],
                ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'critical'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'nonRecoverable'],
            ];
        } elseif ($value_oid == 'voltageProbeStatus' || $value_oid == 'amperageProbeStatus' || $value_oid == 'systemBatteryStatus') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
                ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'nonCriticalUpper'],
                ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'criticalUpper'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'nonRecoverableUpper'],
                ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'nonCriticalLower'],
                ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'criticalLower'],
                ['value' => 9, 'generic' => 2, 'graph' => 0, 'descr' => 'nonRecoverableLower'],
                ['value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
            ];
        }

        // Create State Index
        create_state_index($value_oid, $states);

        foreach ($temp as $index => $entry) {
            if ($value_oid == 'memoryDeviceStatus') {
                $descr = $entry[$descr_oid] . ', ' . $entry['memoryDeviceSize'] / 1024 . ' MB';
            } else {
                $descr = $entry[$descr_oid];
            }

            //Discover Sensors
            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                $num_oid . $index,
                $index,
                $value_oid,
                $descr,
                1,
                1,
                null,
                null,
                null,
                null,
                $entry[$value_oid],
                'snmp',
                $index
            );

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $value_oid, $index);
        }
    }
}
