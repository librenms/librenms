<?php
/*
 * LibreNMS Quanta memory information module
 *
 * Copyright (c) 2017 Mark Guzman <segfault@hasno.info>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == "quanta") {
    d_echo('Quanta Memory:');
    //QUANTA-LB6M-REF-MIB::agentSwitchCpuProcessMemFree
    $avail = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.1.0', '-Oqv');
    //QUANTA-LB6M-REF-MIB::agentSwitchCpuProcessMemAvailable
    $total = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.2.0', '-Oqv');
    $used = $total - $avail;
    $percent = ($used / $total * 100);

    if ((is_numeric($total)) && (is_numeric($avail))) {
        discover_mempool($valid_mempool, $device, 0, 'quanta', 'Memory', '1', null, null);
    }
}
