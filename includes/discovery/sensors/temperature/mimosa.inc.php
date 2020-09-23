<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('Mimosa');
$oid = '.1.3.6.1.4.1.43356.2.1.2.1.8.0';
$index = 0;
$sensor_type = 'mimosaInternalTemp';
$descr = 'Internal Temp';
$divisor = 10;
$temperature = (snmp_get($device, $oid, '-Oqv') / $divisor);
if (is_numeric($temperature)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensor_type, $descr, $divisor, '1', '0', null, null, '65', $temperature);
}
