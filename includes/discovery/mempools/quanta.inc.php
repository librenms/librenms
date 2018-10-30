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

    $avail = snmp_get($device, 'agentSwitchCpuProcessMemFree.0', '-OqvU', 'NETGEAR-SWITCHING-MIB');
    $total = snmp_get($device, 'agentSwitchCpuProcessMemAvailable.0', '-OqvU', 'NETGEAR-SWITCHING-MIB');

    if ((is_numeric($total)) && (is_numeric($avail))) {
        discover_mempool($valid_mempool, $device, 0, 'quanta', 'Memory', '1', null, null);
    }
}
