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

// Extreme/Avaya Memory for VOSS
// rcKhiSlotMemUsed 1.3.6.1.4.1.2272.1.85.10.1.1.6.1
// rcKhiSlotMemFree 1.3.6.1.4.1.2272.1.85.10.1.1.7.1
// rcKhiSlotMemUtil 1.3.6.1.4.1.2272.1.85.10.1.1.8.1

if ($device['os'] == 'voss') {
    $memutil = trim(snmp_get($device, '.1.3.6.1.4.1.2272.1.85.10.1.1.8.1', '-OvQ'));
    if (is_numeric($memutil)) {
        discover_mempool($valid_mempool, $device, 0, 'avaya-vsp', 'VOSS Memory', '1', null, null);
    }
}
