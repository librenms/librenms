<?php

echo 'ZTE ZXDSL:';

// System temperature
$high_limit = 70;
$high_warn_limit = 60;
$low_warn_limit = -20;
$low_limit = -30;

$descr = 'System Temperature';
$valueoid = '.1.3.6.1.4.1.3902.1004.3.1.2.1.0';
$value = snmp_get($device, $valueoid, '-Oqv');
$value = str_replace('"', '', $value);

if (is_numeric($value)) {
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        $valueoid,
        0,
        'zxdsl',
        $descr,
        '1',
        '1',
        $low_limit,
        $low_warn_limit,
        $high_warn_limit,
        $high_limit,
        $value
    );
}
