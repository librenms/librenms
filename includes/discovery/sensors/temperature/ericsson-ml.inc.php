<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Martin Zatloukal <slezi2@pvfree.net>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('ERICSSON-ML');
$oid = '.1.3.6.1.4.1.193.223.2.4.1.1.2.1';
$index = 0;
$sensor_type = 'temperatureRadio';
$descr = 'Internal Temp';
$divisor = 1;
$temperature = (float) snmp_get($device, $oid, '-Oqv', 'PT-MONITOR-MIB');

if ($temperature != 0.0) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensor_type, $descr, $divisor, null, null, null, null, null, $temperature);
}
