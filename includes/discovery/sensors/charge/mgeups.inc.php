<?php

echo("APC/MGE UPS ");

$descr   = 'Battery charge remaining';
$oid     = '.1.3.6.1.2.1.33.1.2.4.0';
$value   = snmp_get($device, 'upsEstimatedChargeRemaining.0', '-Oqv', 'UPS-MIB');
$value   = preg_replace('/\D/', '', $value);

if (is_numeric($value) && $value > 0) {
    discover_sensor($valid['sensor'], 'charge', $device, $oid, 1, 'upsEstimatedChargeRemaining', $descr, '1', '1', null, null, null, null, $value);
}
