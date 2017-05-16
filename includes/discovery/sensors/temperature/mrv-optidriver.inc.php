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


if ($device['os'] == 'mrv-optidriver') {
    echo("MRV OptiDriver:");
    // Chassis temperature
    $high_limit         = 70;
    $high_warn_limit    = 65;
    $low_warn_limit     = 5;
    $low_limit          = 0;
    $descr   =  "Chassis Temperature";
    $valueoid     = ".1.3.6.1.4.1.629.200.6.1.1.15.1";
    $value   = snmp_get($device, 'nbsCmmcChassisTemperature.1', '-Ovqs', 'NBS-CMMC-MIB', $config['install_dir'].'/mibs/mrv');
    //$value = str_replace('"', '', $value);
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'temperature', $device, $valueoid, 1, 'mrv-optidriver', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
    }
}
