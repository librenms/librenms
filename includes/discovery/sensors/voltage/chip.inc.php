<?php

$chip = snmp_get($device, '.1.3.6.1.4.1.50000.1.3.1.1.2.72.87', '-Oqv');
if (strpos($chip, 'CHIP') !== false) {
    $sensor_type = "chip_volts";
    $oid = '.1.3.6.1.4.1.50000.3.4.1.2.15.66.97.116.116.101.114.121.95.86.111.108.116.97.103.101.';
    $lowlimit     = 2.75;
    $lowwarnlimit = 2.9;
    $warnlimit    = 4.2;
    $limit        = 4.3;
    $descr = 'Battery voltage';
    $index = '1';
    $value = snmp_get($device, $oid.$index, '-Oqv');
    $value = $value / 1000;
    discover_sensor($valid['sensor'], 'voltage', $device, $oid.$index, $index, $sensor_type, $descr, '1', '1', null, null, null, null, $value);
}
