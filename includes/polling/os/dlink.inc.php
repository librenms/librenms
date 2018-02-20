<?php

$Descr_string  = $device['sysDescr'];
$Descr_chopper = preg_split('/[ ]+/', "$Descr_string");

$hardware = $Descr_chopper[0].' Rev. '.str_replace('"', '', snmp_get($device, '.1.3.6.1.4.1.171.12.11.1.9.4.1.12.1', '-Oqv'));
$version = snmp_get($device, '.1.3.6.1.4.1.171.12.11.1.9.4.1.11.1', '-Oqv');
$serial = snmp_get($device, '.1.3.6.1.4.1.171.12.11.1.9.4.1.17.1', '-Oqv');
