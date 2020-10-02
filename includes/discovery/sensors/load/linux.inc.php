<?php

$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.8';
$value = snmp_get($device, $oids, '-Osqnv');

if (! empty($value)) {
    $type = 'ups-nut';
    $index = 8;
    $limit = 100;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Ups Load';

    discover_sensor($valid['sensor'], 'load', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
