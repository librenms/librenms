<?php

preg_match('/BenuOS\, (.*)\n.Product\:(.*)\n.*\n.*\n Chassis Type \:(.*)/', $device['sysDescr'], $matches);

$version = $matches['1'];
$features = $matches['2'];
$hardware = $matches['3'];

$serial = snmp_get($device, 'benuChassisId.0', '-Ovqs', 'BENU-CHASSIS-MIB');
