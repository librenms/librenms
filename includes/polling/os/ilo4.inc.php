<?php

$hardware = snmp_get($device, "ENTITY-MIB::entPhysicalModelName.1", "-Ovq");
$version = str_replace("Integrated Lights-Out 4 ", "", $device['sysDescr']);
$serial = snmp_get($device, "entPhysicalSerialNum.1", "-OsvQU", "ENTITY-MIB");

?>
