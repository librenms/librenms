<?php
$chip = snmp_get($device, '.1.3.6.1.2.1.1.1.0', '-Oqv');

if (preg_match("/(Linux).+(ntc)/", $chip)) {
    $sensor_type = "chip_battery_charge";
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.10.112.111.119.101.114.45.115.116.97.';
    $lowlimit     = 5;
    $lowwarnlimit = 9;
    $warnlimit    = null;
    $limit        = null;
    $descr = 'Battery charge';
    $index = '116.8';
    $value = snmp_get($device, $oid.$index, '-Oqv');
    discover_sensor($valid['sensor'], 'charge', $device, $oid.$index, $index, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
}
