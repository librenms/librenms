<?php
/*
 * LibreNMS Quanta LB6M memory information
 *
 * Copyright (c) 2017 Mark Guzman <segfault@hasno.info>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$total = snmp_get($device, 'agentSwitchCpuProcessMemAvailable.0', '-OqvU', 'NETGEAR-SWITCHING-MIB');
$free = snmp_get($device, 'agentSwitchCpuProcessMemFree.0', '-OqvU', 'NETGEAR-SWITCHING-MIB');

$mempool['total'] = $total;
$mempool['free'] = $free;
$mempool['used'] = $total - $free;
