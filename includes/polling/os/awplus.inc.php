<?php

//$hardware and $serial use snmp_getnext as the OID for these is not always fixed.
//However, the first OID is the device baseboard. 

$hardware = snmp_getnext($device, "rscBoardName", "-OQv", "AT-RESOURCE-MIB");
$version = snmp_get($device, "currSoftVersion.0", "-OQv", "AT-SETUP-MIB");
$hostname = snmp_get($device, "sysName.0", "-OQv", "SNMPv2-MIB");
$serial = snmp_getnext($device, "rscBoardSerialNumber", "-OQv", "AT-RESOURCE-MIB");
