<?php

// SNMPv2-MIB::sysDescr.0 = STRING: BenuOS, 5.2.0.0
//  Product: MEGApp
//  Built on Tue May 16 14:33:59 EDT 2017
//  Build file : Benu-MEGApp-REL-5.2.0.0-1705161350.tgz
//  Chassis Type : Dell R730

preg_match('/BenuOS\, (.*)\n.Product\:(.*)\n.*\n.*\n Chassis Type \:(.*)/', $poll_device['sysDescr'], $matches);

$version  = $matches['1'];
$features = $matches['2'];
$hardware = $matches['3'];

$serial = snmp_get($device, 'benuChassisId.0', '-Ovqs', 'BENU-CHASSIS-MIB');
