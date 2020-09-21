<?php
/*
 * LibreNMS Lantronix SLC Temperature Sensor Discovery module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Lantronix SLC';

// Chassis temperature
$high_limit = 50;
$high_warn_limit = 45;
$low_warn_limit = 5;
$low_limit = 1;

$descr = 'System Temperature:';
$valueoid = '.1.3.6.1.4.1.244.1.1.6.25.0'; // LANTRONIX-SLC-MIB::slcSystemInternalTemp.0 = INTEGER: 24 Celsius
$value = snmp_get($device, 'slcSystemInternalTemp.0', '-Oqv', 'LANTRONIX-SLC-MIB');
$value = trim($value, 'Celsius');
$value = trim($value, ' ');

if (is_numeric($value)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $valueoid, 1, 'lantronix-slc', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
}
