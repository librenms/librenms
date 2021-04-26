<?php

$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.3';
$value = snmp_get($device, $oids, '-Osqnv');

if (! empty($value)) {
    $type = 'ups-nut';
    $index = 3;
    $limit = 1000;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Time Remaining';

    discover_sensor($valid['sensor'], 'runtime', $device, $oids, $index, $type, $descr, 60, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
