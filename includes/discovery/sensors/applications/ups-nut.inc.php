<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Martin Zatloukal<slezi2@pvfree.net>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.1';
$value = snmp_get($device, $oids, '-Osqnv');

if (!empty($value)) {
    $type = 'ups-nut';
    $index = 1;
    $limit = 100;
    $lowlimit = 0;
    $lowwarnlimit = 10;
    $descr = 'Battery Charge';

    discover_sensor($valid['sensor'], 'ups_nut', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.2';
$value = snmp_get($device, $oids, '-Osqnv');

if (!empty($value)) {
    $type = 'ups-nut';
    $index = 2;
    $limit = 0;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Battery Low';

    discover_sensor($valid['sensor'], 'ups_nut', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.3';
$value = snmp_get($device, $oids, '-Osqnv');

if (!empty($value)) {
    $type = 'ups-nut';
    $index = 3;
    $limit = 0;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Time Remaining';

    discover_sensor($valid['sensor'], 'ups_nut', $device, $oids, $index, $type, $descr, 60, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.4';
$value = snmp_get($device, $oids, '-Osqnv');

if (!empty($value)) {
    $type = 'ups-nut';
    $index = 4;
    $limit = 0;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Battery Voltage';

    discover_sensor($valid['sensor'], 'ups_nut', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.5';
$value = snmp_get($device, $oids, '-Osqnv');

if (!empty($value)) {
    $type = 'ups-nut';
    $index = 5;
    $limit = 0;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Battery Nominal';

    discover_sensor($valid['sensor'], 'ups_nut', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.6';
$value = snmp_get($device, $oids, '-Osqnv');

if (!empty($value)) {
    $type = 'ups-nut';
    $index = 6;
    $limit = 0;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Line Nominal';

    discover_sensor($valid['sensor'], 'ups_nut', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.7';
$value = snmp_get($device, $oids, '-Osqnv');

if (!empty($value)) {
    $type = 'ups-nut';
    $index = 7;
    $limit = 0;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Input Voltage';

    discover_sensor($valid['sensor'], 'ups_nut', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}

unset($oids);
$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.8';
$value = snmp_get($device, $oids, '-Osqnv');

if (!empty($value)) {
    $type = 'ups-nut';
    $index = 8;
    $limit = 0;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Ups Load';

    discover_sensor($valid['sensor'], 'ups_nut', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}
