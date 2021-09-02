<?php

$oid_list = ['bcnSysIdOSRelease.0', 'bcnSysIdSerial.0', 'bcnSysIdPlatform.0'];

$bcn = snmp_get_multi_oid($device, $oid_list, '-OUQs', 'BCN-SYSTEM-MIB');

$version = $bcn[0]['bcnSysIdOSRelease.0'];
$hardware = $bcn[0]['bcnSysIdPlatform.0'];
$serial = $bcn[0]['bcnSysIdSerial.0'];
