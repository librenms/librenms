<?php

echo ' EXTREME-BASE-MIB ';

// Chassis temperature
$high_limit = 65;
$high_warn_limit = 55;
$low_warn_limit = 15;
$low_limit = 5;

$descr = 'Chassis Temperature';
$oid = '.1.3.6.1.4.1.1916.1.1.1.8.0'; // extremeCurrentTemperature
$value = snmp_get($device, $oid, '-Oqv', 'EXTREME-BASE-MIB');

if (is_numeric($value)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 1, 'extreme-temp', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
}
