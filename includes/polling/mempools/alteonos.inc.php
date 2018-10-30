<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Simone Fini <tomfordfirst@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// AlteonOs Memory
// mpMemStatsTotal 1.3.6.1.4.1.1872.2.5.1.2.8.1.0
// mpMemStatsFree 1.3.6.1.4.1.1872.2.5.1.2.8.3.0

$total = snmp_get($device, ".1.3.6.1.4.1.1872.2.5.1.2.8.1.0", '-OvQ');
$free = snmp_get($device, ".1.3.6.1.4.1.1872.2.5.1.2.8.3.0", '-OvQ');
$perc = ($total / $free) * 100;
$used = ($total - $free);
if (is_numeric($used) && is_numeric($free) && is_numeric($perc)) {
    $mempool['total'] = $total;
    $mempool['free']  = $free;
    $mempool['used']  = $used;
    $mempool['perc']  = $perc;
}
