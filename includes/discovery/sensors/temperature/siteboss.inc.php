<?php

/*
 * LibreNMS Temperature Sensor Discovery module for Asentria SiteBoss
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Asentria SiteBoss ';

$tempoids = snmp_walk($device, 'esIndexPC', '-Osqn', 'SITEBOSS-530-STD-MIB');
$tempoids = trim($tempoids);

foreach (explode("\n", $tempoids) as $data) {
    $data = trim($data);
    $data = explode(" ", $data);
    if ($data[1] == '1') {
        $oid = str_replace('.1.3.6.1.4.1.3052.12.1.1.1.1.2.', '', $data[0]);
        $temperature_oid = ".1.3.6.1.4.1.3052.12.1.1.1.1.6.$oid";
        $descr_oid       = ".1.3.6.1.4.1.3052.12.1.1.1.1.4.$oid";
        $descr           = snmp_get($device, $descr_oid, '-Oqv', '');
        $temperature     = str_replace('"', '', snmp_get($device, $temperature_oid, '-Oqv', ''));

        discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'siteboss', $descr, '1', '1', null, null, null, null, 'fahrenheit_to_celsius');
    }
}
