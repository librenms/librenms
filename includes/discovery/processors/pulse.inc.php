<?php
/*
 * LibreNMS Pulse Secure OS information module
 *
 * Copyright (c) 2015 Christophe Martinet Chrisgfx <martinet.christophe@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/
//
// Hardcoded discovery of CPU usage on Pulse Secure devices.
//
if ($device['os'] == 'pulse') {
    echo 'Pulse Secure : ';

    $descr = 'Processor';
    $usage = str_replace('"', "", snmp_get($device, 'PULSESECURE-PSG-MIB::iveCpuUtil.0', '-OvQ'));

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, 'PULSESECURE-PSG-MIB::iveCpuUtil.0', '0', 'pulse-cpu', $descr,
 '100', $usage, null, null);
    }
}
