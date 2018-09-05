<?php

$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.43.67108873', '-OQv'), '"');

preg_match("/Version .*\n/", $device['sysDescr'], $matches);
$version = trim(str_replace('Version ', '', $matches[0]));
