<?php

// Power Status OID (Value : 0 Bad, 1 Good, 2 NotPresent)
$temp = snmpwalk_cache_multi_oid($device, 'sysChassisPowerSupplyTable', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp)) {
    echo 'F5 power supply: ';
    //Create State Index
    $state_name = 'sysChassisPowerSupplyStatus';
    $states = array(
        array('value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'Bad'),
        array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Good'),
        array('value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'NotPresent'),
    );
    create_state_index($state_name, $states);

    foreach ($temp as $index => $data) {
        $descr      = "sysChassisPowerSupplyStatus.".$temp[$index]['sysChassisPowerSupplyIndex'];
        $current    = $data['sysChassisPowerSupplyStatus'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.3.2.2.2.1.2.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))

$temp1 = snmpwalk_cache_multi_oid($device, 'sysCmFailoverStatus', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp1)) {
    echo 'F5 FailOver State: ';
    //Create State Index
    $state_name = 'sysCmFailoverStatusId';
    $states = array(
        array('value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'),
        array('value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'OffLine'),
        array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'ForcedOffline'),
        array('value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Standby'),
        array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'Active'),
    );
    create_state_index($state_name, $states);

    foreach (array_keys($temp1) as $index) {
        $descr      = "sysCmFailoverStatusId.".$temp1[$index]['sysCmFailoverStatusId'];
        $current    = $temp1[$index]['sysCmFailoverStatusId'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.14.3.1.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp1) as $index)
} // End if (is_array($temp1))

$temp1 = snmpwalk_cache_multi_oid($device, 'sysCmSyncStatusId', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp1)) {
    echo 'F5 Sync State: ';
    //Create State Index
    $state_name = 'sysCmSyncStatusId';
    $states = array(
        array('value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'),
        array('value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'syncing'),
        array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'needManualSync'),
        array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'inSync'),
        array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'syncFailed'),
        array('value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'syncDisconnected'),
        array('value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'standalone'),
        array('value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'awaitingInitialSync'),
        array('value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'incompatibleVersion'),
        array('value' => 9, 'generic' => 2, 'graph' => 0, 'descr' => 'partialSync'),
    );
    create_state_index($state_name, $states);

    foreach (array_keys($temp1) as $index) {
        $descr      = "sysCmSyncStatusId.".$index;
        $current    = $temp1[$index]['sysCmSyncStatusId'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.14.1.1.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp1) as $index)
} // End if (is_array($temp1))

$temp1 = snmpwalk_cache_multi_oid($device, 'sysCmFailoverStatusColor', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp1)) {
    echo 'F5 FailOver State Color: ';
    //Create State Index
    $state_name = 'sysCmFailoverStatusColor';
    $states = array(
        array('value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Green: functioning correctly'),
        array('value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Yellow: functioning suboptimally'),
        array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Red: requires attention to function correctly'),
        array('value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'Blue: status is unknown'),
        array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'Gray: intentionally not functioning'),
        array('value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Black: not connected to any peers'),
    );
    create_state_index($state_name, $states);

    foreach (array_keys($temp1) as $index) {
        $descr      = "sysCmFailoverStatusColor.".$index;
        $current    = $temp1[$index]['sysCmFailoverStatusColor'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.14.3.3.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp1) as $index)
} // End if (is_array($temp1))

$temp3 = snmpwalk_cache_multi_oid($device, 'sysChassisFanStatus', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp3)) {
    echo 'F5 FanSpeed State: ';
    //Create State Index
    $state_name = 'sysChassisFanStatus';
    $states = array(
        array('value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'Bad'),
        array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Good'),
        array('value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'NotPresent'),
    );
    create_state_index($state_name, $states);

    foreach (array_keys($temp3) as $index) {
        $descr      = "Fan Speed Status ".$index;
        $current    = $temp3[$index]['sysChassisFanStatus'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.3.2.1.2.1.2.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp3) as $index)
} // End if (is_array($temp3))
