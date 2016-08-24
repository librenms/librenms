<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Cercel Valentin (crc@nuamchefazi.ro)
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'hwg-ste2') {
    d_echo('HWg STE2 Humidity ');
    $oid = '.1.3.6.1.4.1.21796.4.9.3.1.4.1';
    $sensorType = 'ste2_humidity';
    $descr = 'Input 1 Humidity';
    $humidity = snmp_get($device, $oid, '-Osqnv');

    if ($humidity !== false) {
        discover_sensor($valid['sensor'], 'humidity', $device, $oid, '1', $sensorType, $descr, '1', '1', null, null, null, null, $humidity);
    }
}
