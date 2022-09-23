<?php
/*
 * LibreNMS Telco Systems Temperature Sensor Discovery module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (strpos($device['sysObjectID'], '.1.3.6.1.4.1.738.1.5.100') !== false) {
    echo 'Telco Systems:';

    // CPU temperature
    $high_limit = 70;
    $high_warn_limit = 65;
    $low_warn_limit = 5;
    $low_limit = 0;

    $descr = 'CPU Temperature';
    $valueoid = '.1.3.6.1.4.1.738.1.5.100.3.2.3.0'; // PRVT-SWITCH-MIB::reportsHardwareTemperature.0
    $value = snmp_get($device, $valueoid, '-Oqv');
    $value = str_replace('"', '', $value);

    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'temperature', $device, $valueoid, 1, 'binos', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
    }
}
