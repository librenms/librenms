<?php

$f5_chassis = [];
// Get the Chassis Temperature values
//Pull the sysChassisTempTable table from the snmpwalk
$f5_chassis = snmpwalk_cache_multi_oid($device, 'sysChassisTempTable', [], 'F5-BIGIP-SYSTEM-MIB');

if (is_array($f5_chassis)) {
    echo 'sysChassisTempTable: ';

    foreach (array_keys($f5_chassis) as $index) {
        $descr = 'sysChassisTempTemperature.' . $f5_chassis[$index]['sysChassisTempIndex'];
        $current = $f5_chassis[$index]['sysChassisTempTemperature'];
        $sensorType = 'f5';
        $oid = '.1.3.6.1.4.1.3375.2.1.3.2.3.2.1.2.' . $index;

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', null, null, null, null, $current);
    }
}

// Get the CPU Temperature values
$f5cpu = [];
$f5cpu = snmpwalk_cache_multi_oid($device, 'sysCpuSensorTemperature', [], 'F5-BIGIP-SYSTEM-MIB');

if (is_array($f5cpu)) {
    echo 'sysCpuSensorTemperature: ';

    foreach (array_keys($f5cpu) as $index) {
        $slotnum = $f5cpu[$index]['sysCpuSensorSlot'];
        $cpuname = $f5cpu[$index]['sysCpuSensorName'];
        $descr = 'Cpu Temperature slot' . $index;
        $current = $f5cpu[$index]['sysCpuSensorTemperature'];
        $sensorType = 'f5';
        $oid = '.1.3.6.1.4.1.3375.2.1.3.6.2.1.2.' . $index;

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', null, null, null, null, $current);
    }
}
