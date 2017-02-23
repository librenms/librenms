<?php
/*
 * LibreNMS Enterasys memory information module
 *
 * Copyright (c) 2017 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'enterasys') {
    $free = snmp_get($device, 'etsysResourceStorageAvailable.3.ram.0', '-OvQ', 'ENTERASYS-RESOURCE-UTILIZATION-MIB');
    $total  = snmp_get($device, 'etsysResourceStorageSize.3.ram.0', '-OvQ', 'ENTERASYS-RESOURCE-UTILIZATION-MIB');

    if (is_numeric($free) && is_numeric($total)) {
        discover_mempool($valid_mempool, $device, 0, 'enterasys', 'Memory', '1', null, null);
    }
}
