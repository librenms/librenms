<?php

echo("Doing Force10 FTOS ");

## Stats for S-Series

#F10-S-SERIES-CHASSIS-MIB::chStackUnitModelID.1 = STRING: S25-01-GE-24V
#F10-S-SERIES-CHASSIS-MIB::chStackUnitStatus.1 = INTEGER: ok(1)
#F10-S-SERIES-CHASSIS-MIB::chStackUnitDescription.1 = STRING: 24-port E/FE/GE with POE (SB)
#F10-S-SERIES-CHASSIS-MIB::chStackUnitCodeVersion.1 = STRING: 7.8.1.3
#F10-S-SERIES-CHASSIS-MIB::chStackUnitCodeVersionInFlash.1 = STRING:
#F10-S-SERIES-CHASSIS-MIB::chStackUnitSerialNumber.1 = STRING: DL2E9250002
#F10-S-SERIES-CHASSIS-MIB::chStackUnitUpTime.1 = Timeticks: (262804700) 30 days, 10:00:47.00

$sysObjectID = snmp_get($device, "sysObjectID.0", "-Oqvn");
$hardware = rewrite_ftos_hardware($sysObjectID);




#$hardware = snmp_get($device, "atiswitchProductType.0", "-OsvQU", "+AtiSwitch-MIB", "+".$config['mib_dir']."/alliedtelesis");
#$version  = snmp_get($device, "atiswitchSwVersion.0", "-OsvQU", "+AtiSwitch-MIB", "+".$config['mib_dir']."/alliedtelesis");
#$software = snmp_get($device, "atiswitchSw.0", "-OsvQU", "+AtiSwitch-MIB", "+".$config['mib_dir']."/alliedtelesis");

#if($software && $version)
#  $version = $software . " " . $version;

# sysDescr.0 = STRING: "Allied Telesis AT-8624T/2M version 2.9.1-13 11-Dec-2007"
# sysDescr.0 = STRING: "Allied Telesyn Ethernet Switch AT-8012M"
# sysDescr.0 = STRING: "ATI AT-8000S" <------------------------------------- RADLAN ********
# sysDescr.0 = STRING: "Allied Telesyn AT-8624T/2M version 2.8.1-02 05-Sep-2006"
# sysDescr.0 = STRING: "AT-8126XL, AT-S21 version 1.4.2"

# AtiL2-MIB::atiL2SwProduct.0 = STRING: "AT-8326GB"
# AtiL2-MIB::atiL2SwVersion.0 = STRING: "AT-S41 v1.1.6 "

#if(!$hardware && !$version && !$features) {
#  $hardware = snmp_get($device, "atiL2SwProduct.0", "-OsvQU", "+AtiL2-MIB", "+".$config['mib_dir']."/alliedtelesis");
#  $version = snmp_get($device, "atiL2SwVersion.0", "-OsvQU", "+AtiL2-MIB", "+".$config['mib_dir']."/alliedtelesis");
#}

#Allied Telesyn AT-8948 version 2.7.4-02 22-Aug-2005

#list($a,$b,$c,$d,$e,$f) = explode(" ", $sysDescr);
#if(!$hardware && !$version && !$features) {
#  if($a == "Allied" && $d == "version") {
#    $version = $e;
#    $features = $f;
#    $hardware = $c;
#  }
#}#

#if ($a == "Allied" && $d == "Switch") {
#  $hardware = $e;
#}



#$version = str_replace("\"","", $version);
#$features = str_replace("\"","", $features);
#$hardware = str_replace("\"","", $hardware);

include("includes/polling/hr-mib.inc.php");

?>
