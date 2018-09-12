<?php
 /*
 * LibreNMS Signal Strength for the IRD PBI Headends
 * © 2018 Jozef Rebjak <jozefrebjak@icloud.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
echo 'ird_signal_strength';
$signal_oid = '.1.3.6.1.4.1.1070.3.1.1.104.1.1.6.0';
$signal_value = snmp_get($device, $signal_oid, '-Oqv');
$descr = 'Signal Strength';
$divisor = -10;
if (is_numeric($signal_value) && $signal_value > 0) {
    discover_sensor($valid['sensor'], 'signal', $device, $signal_oid, 0, $device['os'], $descr, $divisor, 1, null, null, null, null, $signal_value / $divisor);
}
