<?php

$version  = snmp_get($device, 'rndBrgVersion.0', '-Ovq', 'RADLAN-MIB');
$hardware = str_replace('ATI', 'Allied Telesis', $poll_device['sysDescr']);
$icon     = 'allied';

$features = snmp_get($device, 'rndBaseBootVersion.00', '-Ovq', 'RADLAN-MIB');

$version  = str_replace('"', '', $version);
$features = str_replace('"', '', $features);
$hardware = str_replace('"', '', $hardware);
