<?php

$Descr_string  = $poll_device['sysDescr'];
$Descr_chopper = preg_split('/[ ]+/', "$Descr_string");

$version  = 'Firmware '.$Descr_chopper[1];
$hardware = $Descr_chopper[0].' Rev. '.str_replace('"', '', snmp_get($device, '1.3.6.1.4.1.171.10.76.10.1.2.0', '-Oqv'));
