<?php
$serial = snmp_get($device, 'aixSeSerialNumber.0', '-Oqv', 'IBM-AIX-MIB');
$hardware = snmp_get($device, 'aixSeMachineType.0', '-Oqv', 'IBM-AIX-MIB');
$aix_version = preg_split('/\s+/', $device['sysDescr']);
$version = $aix_version[16];
