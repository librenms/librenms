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

$tables = array(
    // One could add more entrys from deviceGroup, but this will do as a start
    array('processorDeviceStatusTable','.1.3.6.1.4.1.674.10892.1.1100.32.1.5.','processorDeviceStatusStatus','processorDeviceStatusLocationName','MIB-Dell-10892') ,
    array('memoryDeviceTable','.1.3.6.1.4.1.674.10892.1.1100.50.1.5.','memoryDeviceStatus','memoryDeviceLocationName','MIB-Dell-10892') ,
    array('powerSupplyTable','.1.3.6.1.4.1.674.10892.1.600.12.1.5.','powerSupplyStatus','powerSupplyLocationName','MIB-Dell-10892') ,
    array('intrusionTable','.1.3.6.1.4.1.674.10892.1.300.70.1.5.','intrusionStatus','Intrusion','MIB-Dell-10892') ,
    array('controllerTable','.1.3.6.1.4.1.674.10893.1.20.130.1.1.5.','controllerState','controllerName','StorageManagement-MIB') ,
    array('arrayDiskTable','.1.3.6.1.4.1.674.10893.1.20.130.4.1.4.','arrayDiskState','arrayDiskName','StorageManagement-MIB') ,
    array('virtualDiskTable','.1.3.6.1.4.1.674.10893.1.20.140.1.1.4.','virtualDiskState','virtualDiskDeviceName','StorageManagement-MIB') ,
    array('batteryTable','.1.3.6.1.4.1.674.10893.1.20.130.15.1.4.','batteryState','batteryName','StorageManagement-MIB') ,
);

foreach ($tables as $tablevalue) {
    $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], array(), $tablevalue[4]);
    $cur_oid = $tablevalue[1];

    if (is_array($temp)) {
        //Create State Index
        $state_name = $tablevalue[2];
        if ($state_name == 'processorDeviceStatusStatus' || $state_name == 'memoryDeviceStatus' || $state_name == 'powerSupplyStatus' || $state_name == 'intrusionStatus') {
            $states = array(
                array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'),
                array('value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'),
                array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'),
                array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'nonCritical'),
                array('value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'critical'),
                array('value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'nonRecoverable'),
            );
        } elseif ($state_name == 'controllerState') {
            $states = array(
                array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ready'),
                array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'),
                array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'online'),
                array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'offline'),
                array('value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'degraded'),
            );
        } elseif ($state_name == 'arrayDiskState') {
            $states = array(
                array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ready'),
                array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'),
                array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'online'),
                array('value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'offline'),
                array('value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'degraded'),
                array('value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'recovering'),
                array('value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'removed'),
                array('value' => 8, 'generic' => 3, 'graph' => 0, 'descr' => 'non-raid'),
                array('value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'notReady'),
                array('value' => 10, 'generic' => 1, 'graph' => 0, 'descr' => 'resynching'),
                array('value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'replacing'),
                array('value' => 12, 'generic' => 1, 'graph' => 0, 'descr' => 'spinningDown'),
                array('value' => 13, 'generic' => 1, 'graph' => 0, 'descr' => 'rebuild'),
                array('value' => 14, 'generic' => 1, 'graph' => 0, 'descr' => 'noMedia'),
                array('value' => 15, 'generic' => 1, 'graph' => 0, 'descr' => 'formatting'),
                array('value' => 16, 'generic' => 1, 'graph' => 0, 'descr' => 'diagnostics'),
                array('value' => 17, 'generic' => 2, 'graph' => 0, 'descr' => 'predictiveFailure'),
                array('value' => 18, 'generic' => 1, 'graph' => 0, 'descr' => 'initializing'),
                array('value' => 19, 'generic' => 1, 'graph' => 0, 'descr' => 'foreign'),
                array('value' => 20, 'generic' => 1, 'graph' => 0, 'descr' => 'clear'),
                array('value' => 21, 'generic' => 2, 'graph' => 0, 'descr' => 'unsupported'),
                array('value' => 22, 'generic' => 2, 'graph' => 0, 'descr' => 'incompatible'),
                array('value' => 23, 'generic' => 2, 'graph' => 0, 'descr' => 'readOnly'),
            );
        } elseif ($state_name == 'virtualDiskState') {
            $states = array(
                array('value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'),
                array('value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'ready'),
                array('value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'failed'),
                array('value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'online'),
                array('value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'offline'),
                array('value' => 6, 'generic' => 2, 'graph' => 1, 'descr' => 'degraded'),
                array('value' => 7, 'generic' => 1, 'graph' => 1, 'descr' => 'verifying'),
                array('value' => 15, 'generic' => 1, 'graph' => 1, 'descr' => 'resynching'),
                array('value' => 16, 'generic' => 1, 'graph' => 1, 'descr' => 'regenerating'),
                array('value' => 18, 'generic' => 2, 'graph' => 1, 'descr' => 'failedRedundancy'),
                array('value' => 24, 'generic' => 1, 'graph' => 1, 'descr' => 'rebuilding'),
                array('value' => 26, 'generic' => 1, 'graph' => 1, 'descr' => 'formatting'),
                array('value' => 32, 'generic' => 1, 'graph' => 1, 'descr' => 'reconstructing'),
                array('value' => 35, 'generic' => 1, 'graph' => 1, 'descr' => 'initializing'),
                array('value' => 36, 'generic' => 1, 'graph' => 1, 'descr' => 'backgroundInit'),
                array('value' => 52, 'generic' => 2, 'graph' => 1, 'descr' => 'permanentlyDegraded'),
            );
        } elseif ($state_name == 'batteryState') {
            $states = array(
                array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ready'),
                array('value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'failed'),
                array('value' => 6, 'generic' => 2, 'graph' => 1, 'descr' => 'degraded'),
                array('value' => 7, 'generic' => 1, 'graph' => 1, 'descr' => 'reconditioning'),
                array('value' => 9, 'generic' => 1, 'graph' => 1, 'descr' => 'high'),
                array('value' => 10, 'generic' => 1, 'graph' => 1, 'descr' => 'low'),
                array('value' => 12, 'generic' => 1, 'graph' => 1, 'descr' => 'charging'),
                array('value' => 21, 'generic' => 2, 'graph' => 1, 'descr' => 'missing'),
                array('value' => 36, 'generic' => 1, 'graph' => 1, 'descr' => 'learning'),
            );
        }
        create_state_index($state_name, $states);

        foreach ($temp as $index => $entry) {
            if (strpos($index, '54.') === false) { //Because Dell is buggy
                if ($state_name == 'intrusionStatus') {
                    $descr = $tablevalue[3];
                } elseif ($state_name == 'batteryState') {
                    $descr = str_replace('"', "", snmp_get($device, "batteryConnectionControllerName." .$index. "", "-Ovqn", $tablevalue[4])) . ' - ' .$temp[$index][$tablevalue[3]];
                } elseif ($state_name == 'arrayDiskState') {
                    $descr = str_replace('"', "", snmp_get($device, "arrayDiskEnclosureConnectionEnclosureName." .$index. "", "-Ovqn", $tablevalue[4])) . ' - ' .$temp[$index][$tablevalue[3]];
                } else {
                    $descr = clean($temp[$index][$tablevalue[3]]); // Use clean as virtualDiskDeviceName is user defined
                }
                //Discover Sensors
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}
