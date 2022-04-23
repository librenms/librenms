<?php

// Power Status OID (Value : 0 Bad, 1 Good, 2 NotPresent)
// Common States - F5 Power supply and FanSpeed State
$states = [
    ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'Bad'],
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Good'],
    ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'NotPresent'],
];

$temp = snmpwalk_cache_multi_oid($device, 'sysChassisPowerSupplyTable', [], 'F5-BIGIP-SYSTEM-MIB');
if (is_array($temp)) {
    echo 'F5 power supply: ';
    //Create State Index
    $state_name = 'sysChassisPowerSupplyStatus';
    create_state_index($state_name, $states);

    foreach ($temp as $index => $data) {
        $descr = 'sysChassisPowerSupplyStatus.' . $temp[$index]['sysChassisPowerSupplyIndex'];
        $current = $data['sysChassisPowerSupplyStatus'];
        $sensorType = 'f5';
        $oid = '.1.3.6.1.4.1.3375.2.1.3.2.2.2.1.2.' . $index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))

$temp = snmpwalk_cache_multi_oid($device, 'sysChassisFanStatus', [], 'F5-BIGIP-SYSTEM-MIB');
if (is_array($temp)) {
    echo 'F5 FanSpeed State: ';
    //Create State Index
    $state_name = 'sysChassisFanStatus';
    create_state_index($state_name, $states);
    foreach (array_keys($temp) as $index) {
        $descr = 'Fan Speed Status ' . $index;
        $current = $temp[$index]['sysChassisFanStatus'];
        $sensorType = 'f5';
        $oid = '.1.3.6.1.4.1.3375.2.1.3.2.1.2.1.2.' . $index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))

$temp = snmpwalk_cache_multi_oid($device, 'sysCmFailoverStatus', [], 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp)) {
    echo 'F5 FailOver State: ';
    //Create State Index
    $state_name = 'sysCmFailoverStatusId';
    $states = [
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
        ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'OffLine'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'ForcedOffline'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Standby'],
        ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'Active'],
    ];
    create_state_index($state_name, $states);

    foreach (array_keys($temp) as $index) {
        $descr = 'sysCmFailoverStatusId.' . $temp[$index]['sysCmFailoverStatusId'];
        $current = $temp[$index]['sysCmFailoverStatusId'];
        $sensorType = 'f5';
        $oid = '.1.3.6.1.4.1.3375.2.1.14.3.1.' . $index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))

$temp = snmpwalk_cache_multi_oid($device, 'sysCmSyncStatusId', [], 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp)) {
    echo 'F5 Sync State: ';
    //Create State Index
    $state_name = 'sysCmSyncStatusId';
    $states = [
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'syncing'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'needManualSync'],
        ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'inSync'],
        ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'syncFailed'],
        ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'syncDisconnected'],
        ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'standalone'],
        ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'awaitingInitialSync'],
        ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'incompatibleVersion'],
        ['value' => 9, 'generic' => 2, 'graph' => 0, 'descr' => 'partialSync'],
    ];
    create_state_index($state_name, $states);

    foreach (array_keys($temp) as $index) {
        $descr = 'sysCmSyncStatusId.' . $index;
        $current = $temp[$index]['sysCmSyncStatusId'];
        $sensorType = 'f5';
        $oid = '.1.3.6.1.4.1.3375.2.1.14.1.1.' . $index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))

$temp = snmpwalk_cache_multi_oid($device, 'sysCmFailoverStatusColor', [], 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp)) {
    echo 'F5 FailOver State Color: ';
    //Create State Index
    $state_name = 'sysCmFailoverStatusColor';
    $states = [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Green: functioning correctly'],
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Yellow: functioning suboptimally'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Red: requires attention to function correctly'],
        ['value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'Blue: status is unknown'],
        ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'Gray: intentionally not functioning'],
        ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Black: not connected to any peers'],
    ];
    create_state_index($state_name, $states);

    foreach (array_keys($temp) as $index) {
        $descr = 'sysCmFailoverStatusColor.' . $index;
        $current = $temp[$index]['sysCmFailoverStatusColor'];
        $sensorType = 'f5';
        $oid = '.1.3.6.1.4.1.3375.2.1.14.3.3.' . $index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))
