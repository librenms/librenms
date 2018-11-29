<?php

echo 'Vigintos:Temperature';

$temperature_oid = '.1.3.6.1.4.1.27993.5.9.1.3.2.7'; //
$temperature_value = snmp_get($device, $temperature_oid, '-Oqv');
$descr = '(Ampiflier) Temperature';
$divisor = 1;

if (is_numeric($temperature_value) && $temperature_value > 0) {
    discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, 0, $device['os'], $descr, $divisor, 1, null, null, null, null, $temperature_value/ $divisor);
}
