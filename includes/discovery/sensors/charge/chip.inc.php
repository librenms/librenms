
<?php

$chip = snmp_get($device, '.1.3.6.1.4.1.50000.1.3.1.1.2.72.87', '-Oqv');
if (strpos($chip, 'CHIP') !== false) {
    $sensor_type = "chip_volts";
    $oid = '.1.3.6.1.4.1.50000.3.4.1.2.15.66.97.116.116.101.114.121.95.86.111.108.116.97.103.101.';
    $lowlimit     = 5;
    $lowwarnlimit = 9;
    $warnlimit    = null;
    $limit        = null;
    $descr = 'Battery charge';
    $index = '4';
    $value = snmp_get($device, $oid.$index, '-Oqv');
    discover_sensor($valid['sensor'], 'charge', $device, $oid.$index, $index, $sensor_type, $descr, '1', '1', null, null, null, null, $value);
}
