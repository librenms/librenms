<?php

$freq_oid = '.1.3.6.1.4.1.4555.1.1.1.1.3.2.0';
$descr = 'Input';
$current = (snmp_get($device, $freq_oid, '-Oqv') / 10);
$type = 'netvision';
$divisor = 10;
$index = '3.2.0';
discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);

$freq_oid = '.1.3.6.1.4.1.4555.1.1.1.1.4.2.0';
$descr = 'Output';
$current = (snmp_get($device, $freq_oid, '-Oqv') / 10);
$type = 'netvision';
$divisor = 10;
$index = '4.2.0';
discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
