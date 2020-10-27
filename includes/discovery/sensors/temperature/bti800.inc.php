<?php

echo 'BTI800 Temperature:';

$high_limit = '.1.3.6.1.4.1.30005.1.7.100.1.2.1.1.3.14.0';
$high_warn_limit = 65;
$low_warn_limit = -20;
$low_limit = '.1.3.6.1.4.1.30005.1.7.100.1.2.1.1.3.15.0';
$divisor = 1;
$multiplier = 1;

$descr = 'systemTemperature';
$valueoid = '.1.3.6.1.4.1.30005.1.7.100.1.2.1.1.1.7.0';
$value = snmp_get($device, $valueoid, '-Oqv');
$value = str_replace('"', '', $value);

if (is_numeric($value)) {
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        $valueoid,
        $descr,
        'bti800',
        'BTI800,
        $divisor,
        $multiplier,
        $low_limit,
        $low_warn_limit,
        $high_warn_limit,
        $high_limit,
        $value
    );
}
