<?php

/*
 * LibreNMS Temperature Sensor Discovery module for the MRV® OptiDriver® Optical Transport Platform
 *
 * Copyright (c) 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


echo("MRV OptiDriver:");
// Chassis temperature
$descr   =  "Chassis Temperature";
$valueoid    = ".1.3.6.1.4.1.629.200.6.1.1.15.1";
$value   = snmp_get($device, 'nbsCmmcChassisTemperature.1', '-Ovqs', 'NBS-CMMC-MIB');
if (is_numeric($value)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $valueoid, 'nbsCmmcChassisTemperature.1', 'mrv-od', $descr, '1', '1', null, null, null, null, $value);
}
