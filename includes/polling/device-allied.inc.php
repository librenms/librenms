<?php

echo("Doing Allied Telesyn AlliedWare ");

$hardware = snmp_get($device, "atiswitchProductType.0", "-OsvQU", "+AtiSwitch-MIB", "+".$config['mib_dir']."/alliedtelesis");
$version = snmp_get($device, "atiswitchSwVersion.0", "-OsvQU", "+AtiSwitch-MIB", "+".$config['mib_dir']."/alliedtelesis");
$features = snmp_get($device, "atiswitchSw.0", "-OsvQU", "+AtiSwitch-MIB", "+".$config['mib_dir']."/alliedtelesis");

# sysDescr.0 = STRING: "Allied Telesis AT-8624T/2M version 2.9.1-13 11-Dec-2007"
# sysDescr.0 = STRING: "Allied Telesyn Ethernet Switch AT-8012M"
# sysDescr.0 = STRING: "ATI AT-8000S" <------------------------------------- RADLAN ********
# sysDescr.0 = STRING: "Allied Telesyn AT-8624T/2M version 2.8.1-02 05-Sep-2006"
# sysDescr.0 = STRING: "AT-8126XL, AT-S21 version 1.4.2"


include("hr-mib.inc.php");

?>
