<?php

$fnSysVersion = snmp_get($device, '.1.3.6.1.4.1.388.11.2.2.1.3.2.0', '-Ovq');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.388.11.2.2.1.1.0', '-Ovq'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.388.11.2.2.1.3.2.0', '-Ovq'), '"');
// preg_match("/HW=(^\s]+)/",$sysDescr,$hardwarematches);
preg_match('/\s+[^\s]+/', $device['sysDescr'], $hardwarematches);
$hardware = $hardwarematches[0];
