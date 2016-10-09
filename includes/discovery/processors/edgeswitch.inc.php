<?php
/*
 * LibreNMS Ubiquiti EdgeSwitch CPU information module
 *
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'edgeswitch') {
    //SNMPv2-SMI::enterprises.4413.1.1.1.1.4.9.0
    d_echo('EdgeSwitch CPU usage:');
    $descr = 'Processor';
    $proc_usage = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0', '-Ovq');
    preg_match('/([0-9]+.[0-9]+)/', $proc_usage, $usage);
    if (is_numeric($usage[0])) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0', '0', 'edgeswitch', $descr, '1', $usage[0], null, null);
    }
}
