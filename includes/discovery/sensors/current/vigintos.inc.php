<?php

echo 'Vigintos:Current';

$current1_oid = '.1.3.6.1.4.1.27993.5.9.1.3.2.5'; //
$current1_value = snmp_get($device, $current1_oid, '-Oqv');
$descr = '(Ampiflier) Current 1';
$divisor = 1;

if (is_numeric($current1_value) && $current1_value > 0) {
    discover_sensor($valid['sensor'], 'current', $device, $current1_oid, 0, $device['os'], $descr, $divisor, 1, null, null, null, null, $current1_value/ $divisor);
}

$current2_oid = '.1.3.6.1.4.1.27993.5.9.1.3.2.6'; //
$current2_value = snmp_get($device, $current2_oid, '-Oqv');
$descr = '(Ampiflier) Current 2';
$divisor = 1;

if (is_numeric($current2_value) && $current2_value > 0) {
    discover_sensor($valid['sensor'], 'current', $device, $current2_oid, 1, $device['os'], $descr, $divisor, 1, null, null, null, null, $current2_value/ $divisor);
}
