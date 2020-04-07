<?php
$oidBase = '.1.3.6.1.4.1.24693.1.1.';
$voltageOids = array();
$sysOid = explode(".", $device["sysObjectID"]);
$modelNumber = $sysOid[count($sysOid)-1];

if(substr($device["sysDescr"], 0, 6) == "FB2900"){
    // It's a 2900
    $monitoringMib = "FB2900-MONITORING";
    $voltageOids = array(
        "1" => array("description" => "1.1V Reference Voltage"),
        "2" => array("description" => "1.325V Reference Voltage"),
        "3" => array("description" => "1.35V Reference Voltage"),
        "4" => array("description" => "3.3V Reference Voltage"),
        "5" => array("description" => "5.0V Reference Voltage"),
        "6" => array("description" => "Power Supply Voltage"),
        "7" => array("description" => "True Random Number Generator Voltage"),
    );
}elseif((substr($device["sysDescr"], 0, 6) == "FB6000") ||
        (($modelNumber >= 6000) && ($modelNumber <= 7000))){
    // It's a 6000
    $voltageOids = array(
        "1" => array("description" => "PSU A Output Voltage"),
        "2" => array("description" => "PSU B Output Voltage"),
        "3" => array("description" => "+12V"),
        "4" => array("description" => "+3.3V"),
        "5" => array("description" => "+1.8V"),
        "6" => array("description" => "+1.2V"),
        "7" => array("description" => "+1.1V"),
        "8" => array("description" => "+3.3V Fan Power"),
        "9" => array("description" => "+1.2V Fan Power"),
    );
}

foreach($voltageOids as $oid => $cfg){
    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        $oidBase . $oid,
        $oid,
        'firebrick',
        $cfg["description"],
        (isset($cfg["divisor"]) ? $cfg["divisor"] : '1000'),
        '1');
}
