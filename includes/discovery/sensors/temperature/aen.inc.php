<?php
/*
 * LibreNMS Accedian MetroNID Temperature Sensor Discovery module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Accedian MetroNID';

// Chassis temperature
$high_limit = 90;
$high_warn_limit = 85;
$low_warn_limit = 5;
$low_limit = 1;

$descroid = '.1.3.6.1.4.1.22420.1.1.12.1.7.1'; // acdDescTsEntry.7.1
$descr = snmp_get($device, $descroid, '-Oqv');
$descr = str_replace('"', '', $descr);
$valueoid = '.1.3.6.1.4.1.22420.1.1.12.1.2.1'; // acdDescTsCurrentTemp.1
$value = snmp_get($device, $valueoid, '-Oqv');

if (is_numeric($value)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $valueoid, 1, 'metronid', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
}
