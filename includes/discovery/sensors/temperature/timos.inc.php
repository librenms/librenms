<?php

$description_root_oid       = '.1.3.6.1.4.1.6527.3.1.2.2.1.8.1.8';
$sensor_present_root_oid    = '.1.3.6.1.4.1.6527.3.1.2.2.1.8.1.17';
$temperature_root_oid       = '.1.3.6.1.4.1.6527.3.1.2.2.1.8.1.18';
$threshold_root_oid         = '.1.3.6.1.4.1.6527.3.1.2.2.1.8.1.19';

$temp_sensors = snmpwalk_cache_oid($device, 'tmnxHwID', $temp_sensors = array(), 'TIMETRA-CHASSIS-MIB', 'aos');
$temp_sensors = snmpwalk_cache_oid($device, $description_root_oid, $temp_sensors, 'TIMETRA-CHASSIS-MIB', 'aos');
$temp_sensors = snmpwalk_cache_oid($device, $sensor_present_root_oid, $temp_sensors, 'TIMETRA-CHASSIS-MIB', 'aos');
$temp_sensors = snmpwalk_cache_oid($device, $temperature_root_oid, $temp_sensors, 'TIMETRA-CHASSIS-MIB', 'aos');
$temp_sensors = snmpwalk_cache_oid($device, $threshold_root_oid, $temp_sensors, 'TIMETRA-CHASSIS-MIB', 'aos');

foreach ($temp_sensors as $sub_oid => $component) {
    $descr             = $component['tmnxHwName'];
    $temp_present      = $component['tmnxHwTempSensor'];
    $temperature       = $component['tmnxHwTemperature'];
    $temp_thresh       = $component['tmnxHwTempThreshold'];
    if ($temp_present == true && $descr != '' && $temperature != -1) {
        $temperature_oid = $temperature_root_oid . '.' . $sub_oid;
        discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, "tmnxHwTemperature.$sub_oid", 'nokia', $descr, '1', '1', null, null, null, $temp_thresh, $temperature);
    }
}

unset(
    $descr,
    $temp_present,
    $temperature,
    $temp_thresh,
    $temp_sensors,
    $description_root_oid,
    $sensor_present_root_oid,
    $temperature_root_oid,
    $threshold_root_oid
);
