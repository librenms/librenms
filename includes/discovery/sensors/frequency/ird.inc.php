<?php
 /*
 * LibreNMS Satellite Frequency Sensor for the IRD PBI Headends
 * © 2018 Jozef Rebjak <jozefrebjak@icloud.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
echo 'ird_sat_frequency';
$freq_oid = '.1.3.6.1.4.1.1070.3.1.1.104.3.2.0';
$freq_value = snmp_get($device, $freq_oid, '-Oqv');
$descr = 'Satellite Frequency';
$multiplier = 1000000;
 if (is_numeric($freq_value) && $freq_value > 0) {
    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, 0, $device['os'], $descr, $multiplier,  1, null, null, null, null, $freq_value * $multiplier);
}
