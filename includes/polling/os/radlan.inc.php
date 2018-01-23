<?php

// sysDescr.0 = STRING: "ATI AT-8000S"
// sysDescr.0 = STRING: 48-port 10/100/1000 Ethernet Switch
// sysDescr.0 = STRING: 24-port 10/100/1000 Ethernet Switch with PoE

$hardware = snmp_getnext($device, 'entPhysicalDescr.64', '-OsvQU', 'ENTITY-MIB');
$version  = snmp_get($device, 'rndBrgVersion.0', '-OsvQU', 'RADLAN-MIB');
$serial   = snmp_getnext($device, 'entPhysicalSerialNum.64', '-OsvQU', 'ENTITY-MIB');

$version  = str_replace('"', '', $version);
$features = str_replace('"', '', $features);
$hardware = str_replace('"', '', $hardware);
