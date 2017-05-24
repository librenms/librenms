<?php
$chip = snmp_get($device, '.1.3.6.1.4.1.50000.1.3.1.1.2.72.87', '-Oqv');
if (strpos($chip, 'CHIP') !== false) {
    $sensor_type = "chip_currents";
    $oid = '.1.3.6.1.4.1.50000.3.4.1.2.15.66.97.116.116.101.114.121.95.86.111.108.116.97.103.101.';
    $lowlimit     = 0;
    $lowwarnlimit = null;
    $warnlimit    = null;
    $limit        = null;
    $descr = 'Discharge current';
    $current = '2';
    $value = snmp_get($device, $oid.$current, '-Oqv');
    discover_sensor($valid['sensor'], 'current', $device, $oid.$current, $current, $sensor_type, $descr, '1', '1', null, null, null, null, $value);
    $descr = 'Charge current';
    $current = '3';
    $value = snmp_get($device, $oid.$current, '-Oqv');
    $value = $value / 1000;
    discover_sensor($valid['sensor'], 'current', $device, $oid.$current, $current, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
}
