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
    // One could add more entrys from deviceGroup, but this will do as a start
    ['processorDeviceStatusTable', '.1.3.6.1.4.1.674.10892.1.1100.32.1.5.', 'processorDeviceStatusStatus', 'processorDeviceStatusLocationName', 'MIB-Dell-10892'],
    ['memoryDeviceTable', '.1.3.6.1.4.1.674.10892.1.1100.50.1.5.', 'memoryDeviceStatus', 'memoryDeviceLocationName', 'MIB-Dell-10892'],
    ['powerSupplyTable', '.1.3.6.1.4.1.674.10892.1.600.12.1.5.', 'powerSupplyStatus', 'powerSupplyLocationName', 'MIB-Dell-10892'],
    ['intrusionTable', '.1.3.6.1.4.1.674.10892.1.300.70.1.5.', 'intrusionStatus', 'Intrusion', 'MIB-Dell-10892'],
    ['controllerTable', '.1.3.6.1.4.1.674.10893.1.20.130.1.1.5.', 'controllerState', 'controllerName', 'StorageManagement-MIB', 'dell'],
    ['arrayDiskTable', '.1.3.6.1.4.1.674.10893.1.20.130.4.1.4.', 'arrayDiskState', 'arrayDiskName', 'StorageManagement-MIB', 'dell'],
    ['virtualDiskTable', '.1.3.6.1.4.1.674.10893.1.20.140.1.1.4.', 'virtualDiskState', 'virtualDiskDeviceName', 'StorageManagement-MIB', 'dell'],
    ['batteryTable', '.1.3.6.1.4.1.674.10893.1.20.130.15.1.4.', 'batteryState', 'batteryName', 'StorageManagement-MIB', 'dell'],
];

foreach ($tables as $tablevalue) {
    $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], [], $tablevalue[4]);
    $cur_oid = $tablevalue[1];

    if (is_array($temp)) {
        //Create State Index
        $state_name = 'dell.' . $tablevalue[2];
        if ($state_name == 'dell.processorDeviceStatusStatus' || $state_name == 'dell.memoryDeviceStatus' || $state_name == 'dell.powerSupplyStatus' || $state_name == 'dell.intrusionStatus') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
                ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'nonCritical'],
                ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'critical'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'nonRecoverable'],
            ];
        } elseif ($state_name == 'dell.controllerState') {
            $states = [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ready'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'online'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'offline'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'degraded'],
            ];
        } elseif ($state_name == 'dell.arrayDiskState') {
            $states = [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ready'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'online'],
                ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'offline'],
                ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'degraded'],
                ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'recovering'],
                ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'removed'],
                ['value' => 8, 'generic' => 3, 'graph' => 0, 'descr' => 'non-raid'],
                ['value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'notReady'],
                ['value' => 10, 'generic' => 1, 'graph' => 0, 'descr' => 'resynching'],
                ['value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'replacing'],
                ['value' => 12, 'generic' => 1, 'graph' => 0, 'descr' => 'spinningDown'],
                ['value' => 13, 'generic' => 1, 'graph' => 0, 'descr' => 'rebuild'],
                ['value' => 14, 'generic' => 1, 'graph' => 0, 'descr' => 'noMedia'],
                ['value' => 15, 'generic' => 1, 'graph' => 0, 'descr' => 'formatting'],
                ['value' => 16, 'generic' => 1, 'graph' => 0, 'descr' => 'diagnostics'],
                ['value' => 17, 'generic' => 2, 'graph' => 0, 'descr' => 'predictiveFailure'],
                ['value' => 18, 'generic' => 1, 'graph' => 0, 'descr' => 'initializing'],
                ['value' => 19, 'generic' => 1, 'graph' => 0, 'descr' => 'foreign'],
                ['value' => 20, 'generic' => 1, 'graph' => 0, 'descr' => 'clear'],
                ['value' => 21, 'generic' => 2, 'graph' => 0, 'descr' => 'unsupported'],
                ['value' => 22, 'generic' => 2, 'graph' => 0, 'descr' => 'incompatible'],
                ['value' => 23, 'generic' => 2, 'graph' => 0, 'descr' => 'readOnly'],
            ];
        } elseif ($state_name == 'dell.virtualDiskState') {
            $states = [
                ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'ready'],
                ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'failed'],
                ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'online'],
                ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'offline'],
                ['value' => 6, 'generic' => 2, 'graph' => 1, 'descr' => 'degraded'],
                ['value' => 7, 'generic' => 1, 'graph' => 1, 'descr' => 'verifying'],
                ['value' => 15, 'generic' => 1, 'graph' => 1, 'descr' => 'resynching'],
                ['value' => 16, 'generic' => 1, 'graph' => 1, 'descr' => 'regenerating'],
                ['value' => 18, 'generic' => 2, 'graph' => 1, 'descr' => 'failedRedundancy'],
                ['value' => 24, 'generic' => 1, 'graph' => 1, 'descr' => 'rebuilding'],
                ['value' => 26, 'generic' => 1, 'graph' => 1, 'descr' => 'formatting'],
                ['value' => 32, 'generic' => 1, 'graph' => 1, 'descr' => 'reconstructing'],
                ['value' => 35, 'generic' => 1, 'graph' => 1, 'descr' => 'initializing'],
                ['value' => 36, 'generic' => 1, 'graph' => 1, 'descr' => 'backgroundInit'],
                ['value' => 52, 'generic' => 2, 'graph' => 1, 'descr' => 'permanentlyDegraded'],
            ];
        } elseif ($state_name == 'dell.batteryState') {
            $states = [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ready'],
                ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'failed'],
                ['value' => 6, 'generic' => 2, 'graph' => 1, 'descr' => 'degraded'],
                ['value' => 7, 'generic' => 1, 'graph' => 1, 'descr' => 'reconditioning'],
                ['value' => 9, 'generic' => 1, 'graph' => 1, 'descr' => 'high'],
                ['value' => 10, 'generic' => 1, 'graph' => 1, 'descr' => 'low'],
                ['value' => 12, 'generic' => 1, 'graph' => 1, 'descr' => 'charging'],
                ['value' => 21, 'generic' => 2, 'graph' => 1, 'descr' => 'missing'],
                ['value' => 36, 'generic' => 1, 'graph' => 1, 'descr' => 'learning'],
            ];
        }
        create_state_index($state_name, $states);

        foreach ($temp as $index => $entry) {
            if (strpos($index, '54.') === false) { //Because Dell is buggy
                if ($state_name == 'dell.intrusionStatus') {
                    $descr = $tablevalue[3];
                } elseif ($state_name == 'dell.batteryState') {
                    $descr = str_replace('"', '', snmp_get($device, 'batteryConnectionControllerName.' . $index . '', '-Ovqn', $tablevalue[4])) . ' - ' . $temp[$index][$tablevalue[3]];
                } elseif ($state_name == 'dell.arrayDiskState') {
                    $descr = str_replace('"', '', snmp_get($device, 'arrayDiskEnclosureConnectionEnclosureName.' . $index . '', '-Ovqn', $tablevalue[4])) . ' - ' . $temp[$index][$tablevalue[3]];
                } else {
                    $descr = strip_tags($temp[$index][$tablevalue[3]]); // Use clean as virtualDiskDeviceName is user defined
                }
                //Discover Sensors
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, $index, $state_name, $descr, 1, 1, null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}
