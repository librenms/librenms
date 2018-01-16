<?php

$version = trim(snmp_get($device, 'accessSwitchFWVersion.0', '-OQv', 'ZYXEL-AS-MIB'), '"');

preg_match('/IES-(\d)*/', $device['sysDescr'], $matches);
$hardware = $matches[0];
