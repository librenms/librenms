<?php
/*
 * LibreNMS Quanta CPU information module
 *
 * Copyright (c) 2017 Mark Guzman <segfault@hasno.info
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'quanta') {
    d_echo('Quanta CPU usage:');
    $descr = 'Processor';
    $proc_oid = 'agentSwitchCpuProcessTotalUtilization.0';
    $proc_oid_num = '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0';
    $proc_usage = snmp_get($device, $proc_oid, '-Ovq', 'NETGEAR-SWITCHING-MIB');
    preg_match('/([0-9]+.[0-9]+)/', $proc_usage, $usage);
    if (is_numeric($usage[0])) {
        discover_processor($valid['processor'], $device, $proc_oid_num, '0', 'quanta', $descr, '1', $usage[0], null, null);
    }
}
