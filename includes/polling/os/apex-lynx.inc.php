<?php

$version = snmp_get($device, 'sysOSVer.0', '-OQv', 'GIGA-PLUS-MIB');
$hardware = 'Trango ' . $device['sysDescr'];
