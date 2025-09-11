<?php

if (preg_match('/(Linux).+(ntc)/', $device['sysDescr'])) {
    $sensor_type = 'chip_battery_charge';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.10.112.111.119.101.114.45.115.116.97.';
    $lowlimit = 5;
    $lowwarnlimit = 9;
    $warnlimit = null;
    $limit = null;
    $descr = 'Battery charge';
    $index = '116.8';
    $value = snmp_get($device, $oid . $index, '-Oqv');
    if (is_numeric($value)) {
        discover_sensor(null, 'charge', $device, $oid . $index, $index, $sensor_type, $descr, 1, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    }
}
