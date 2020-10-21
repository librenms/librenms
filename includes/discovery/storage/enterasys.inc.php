<?php
/*
 * Copyright (c) 2017 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
if ($device['os'] == 'enterasys') {
    $fstype = 'Flash';
    $descr = 'Internal Flash Storage';
    $units = 1024;
    $index = 0;
    $free = snmp_get($device, 'etsysResourceStorageAvailable.3.flash.0', '-OvQ', 'ENTERASYS-RESOURCE-UTILIZATION-MIB');
    $total = snmp_get($device, 'etsysResourceStorageSize.3.flash.0', '-OvQ', 'ENTERASYS-RESOURCE-UTILIZATION-MIB');
    $used = $total - $free;
    if (is_numeric($free) && is_numeric($total)) {
        discover_storage($valid_storage, $device, $index, $fstype, 'enterasys', $descr, $total, $units, $used);
    }
}
