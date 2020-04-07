<?php
$sysOid = explode(".", $device["sysObjectID"]);
$modelNumber = $sysOid[count($sysOid)-1];

$oidBase = '.1.3.6.1.4.1.24693.1.3.';
$fanOids = array();

if((substr($device["sysDescr"], 0, 6) == "FB6000") ||
   (($modelNumber >= 6000) && ($modelNumber <= 7000))){
    // It's a 6000
    $fanOids = array(
        "1" => array("description" => "Fan 1 Speed (rpm)"),
        "2" => array("description" => "Fan 2 Speed (rpm)"),
    );
}

foreach($fanOids as $oid => $cfg){
    discover_sensor(
        $valid['sensor'],
        'fanspeed',
        $device,
        $oidBase . $oid,
        $oid,
        'firebrick',
        $cfg["description"],
        (isset($cfg["divisor"]) ? $cfg["divisor"] : '1'),
        '1',
        1000,
        2000,
        8000,
        10000);
}
