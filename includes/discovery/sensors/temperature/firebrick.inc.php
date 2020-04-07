<?php
$sysOid = explode(".", $device["sysObjectID"]);
$modelNumber = $sysOid[count($sysOid)-1];

$monitoringMib = null;
if(substr($device["sysDescr"], 0, 6) == "FB2900"){
    // It's a 2900
    $monitoringMib = "FB2900-MONITORING";
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        '.1.3.6.1.4.1.24693.1.2.1',
        '1',
        'firebrick',
        'CPU Temperature',
        '1000',
        '1',
        10,
        20,
        50,
        70);
}elseif((substr($device["sysDescr"], 0, 6) == "FB6000") ||
        (($modelNumber >= 6000) && ($modelNumber <= 7000))){
    // It's a 6000
    $monitoringMib = "FB6000-MONITORING";
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        '.1.3.6.1.4.1.24693.1.2.1',
        '1',
        'firebrick',
        'Fan Controller Temperature',
        '1000',
        '1',
        10,
        20,
        50,
        70);
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        '.1.3.6.1.4.1.24693.1.2.2',
        '2',
        'firebrick',
        'CPU Temperature',
        '1000',
        '1',
        10,
        20,
        60,
        80);
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        '.1.3.6.1.4.1.24693.1.2.1',
        '3',
        'firebrick',
        'RTC Temperature',
        '1000',
        '1',
        10,
        20,
        50,
        70);
}
