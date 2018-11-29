<?php

echo 'Vigintos:Voltage';

$voltage_oid = '.1.3.6.1.4.1.27993.5.9.1.3.2.4'; //
$voltage_value = snmp_get($device, $voltage_oid, '-Oqv');
$descr = '(Ampiflier) Voltage';
$divisor = 1;

if (is_numeric($voltage_value) && $voltage_value > 0) {
    discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, 0, $device['os'], $descr, $divisor, 1, null, null, null, null, $voltage_value/ $divisor);
}
