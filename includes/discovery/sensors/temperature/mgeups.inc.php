<?php

echo("APC/MGE UPS ");

$descr   = 'Battery Temperature';
$oid     = '.1.3.6.1.2.1.33.1.2.7.0';
$value   = snmp_get($device, 'upsBatteryTemperature.0', '-Oqv', 'UPS-MIB');
$value   = preg_replace('/\D/', '', $value);

if (is_numeric($value) && $value > 0) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 1, 'upsBatteryTemperature', $descr, '1', '1', null, null, null, null, $value);
}
