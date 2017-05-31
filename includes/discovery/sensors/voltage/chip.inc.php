<?php

$chip = snmp_get($device, '.1.3.6.1.2.1.1.1.0', '-Oqv');
if (strpos($chip, 'chip') !== false) {
    $sensor_type = "chip_volts";
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.10.112.111.119.101.114.45.115.116.97.116.';
    $lowlimit     = null;
    $lowwarnlimit = null;
    $warnlimit    = null;
    $limit        = null;
    $descr = 'AC IN voltage';
    $index = '2';
    $value = snmp_get($device, $oid.$index, '-Oqv');
    discover_sensor($valid['sensor'], 'voltage', $device, $oid.$index, $index, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    $descr = 'VBUS voltage';
    $index = '4';
    $value = snmp_get($device, $oid.$index, '-Oqv');
    discover_sensor($valid['sensor'], 'voltage', $device, $oid.$index, $index, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    $descr = 'Battery voltage';
    $index = '6';
    $value = snmp_get($device, $oid.$index, '-Oqv');
    discover_sensor($valid['sensor'], 'voltage', $device, $oid.$index, $index, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
}
