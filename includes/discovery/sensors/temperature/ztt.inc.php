<?php

/**
 * For ZTT MSJ devices
 */

// Battery pack temperature
$high_limit = 50;
$high_warn_limit = 45;
$low_warn_limit = 5;
$low_limit = 1;

$descr = 'Batterypacktemperature';
$valueoid = '.1.3.6.1.4.1.49692.1.1.1.1.17.1';
$value = snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.17.1', '-Oqv');
$value = trim($value, 'Celsius');
$value = trim($value, ' ');

if (is_numeric($value)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $valueoid, 1, 'Batterypack', $descr, '1000', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
}
