<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Dell UPS: ';
$temp = snmp_get($device, 'physicalBatterySecondsRemaining.0', '-Ovqe', 'DELL-SNMP-UPS-MIB');
if (is_numeric($temp) && ! is_null($temp)) {
    $oid = '.1.3.6.1.4.1.674.10902.2.120.5.3.0';
    $descr = 'Runtime';
    $divisor = '60';
    $current = $temp / 60;
    $low_limit = 5;
    $low_limit_warn = 10;
    $warn_limit = 2000;
    $high_limit = 3000;
    discover_sensor($valid['sensor'], 'runtime', $device, $oid, '0', 'dell-ups', $descr, $divisor, '1', $low_limit, $low_limit_warn, $warn_limit, $high_limit, $current);
}
