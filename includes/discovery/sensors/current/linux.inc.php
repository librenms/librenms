<?php

if (preg_match('/(Linux).+(ntc)/', $device['sysDescr'])) {
    $sensor_type = 'chip_currents';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.10.112.111.119.101.114.45.115.116.97.';
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $warnlimit = null;
    $limit = null;
    $descr = 'AC IN current';
    $current = '116.3';
    $value = snmp_get($device, $oid . $current, '-Oqv');
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'current', $device, $oid . $current, $current, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    }
    $descr = 'VBUS current';
    $current = '116.5';
    $value = snmp_get($device, $oid . $current, '-Oqv');
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'current', $device, $oid . $current, $current, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    }
    $descr = 'Battery current';
    $current = '116.7';
    $value = snmp_get($device, $oid . $current, '-Oqv');
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'current', $device, $oid . $current, $current, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    }
}
