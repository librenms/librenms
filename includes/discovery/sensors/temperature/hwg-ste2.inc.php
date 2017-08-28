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

d_echo('HWg STE2 Temperature ');
$oid = '.1.3.6.1.4.1.21796.4.9.3.1.4.2';
$sensorType = 'ste2_temp';
$descr = 'Input 2 Temperature';
$temperature = snmp_get($device, $oid, '-Osqnv');

if ($temperature !== false) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, '1', $sensorType, $descr, '1', '1', null, null, null, null, $temperature);
}
