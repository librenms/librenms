<?php

$oid = '.1.3.6.1.4.1.30631.1.9.1.1.5.0';
$current = (snmp_get($device, $oid, '-Oqv') / 10);
discover_sensor($valid['sensor'], 'voltage', $device, $oid, 0, 'sti410c', 'Supply voltage', 10, 1, null, null, null, null, $current);
