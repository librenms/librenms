<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Daniel Cox <danielcoxman@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// Extreme/Avaya VSP Memory w/ VOSS
// rcKhiSlotMemUsed 1.3.6.1.4.1.2272.1.85.10.1.1.6.1
// rcKhiSlotMemFree 1.3.6.1.4.1.2272.1.85.10.1.1.7.1
// rcKhiSlotMemUtil 1.3.6.1.4.1.2272.1.85.10.1.1.8.1

$used = (snmp_get($device, "1.3.6.1.4.1.2272.1.85.10.1.1.6.1", '-OvQ') * 1000);
$free = (snmp_get($device, "1.3.6.1.4.1.2272.1.85.10.1.1.7.1", '-OvQ') * 1000);
$perc = snmp_get($device, "1.3.6.1.4.1.2272.1.85.10.1.1.8.1", '-OvQ');
$total = ($used + $free);
if (is_numeric($used) && is_numeric($free) && is_numeric($perc)) {
    $mempool['total'] = $total;
    $mempool['free']  = $free;
    $mempool['used']  = $used;
    $mempool['perc']  = $perc;
}
