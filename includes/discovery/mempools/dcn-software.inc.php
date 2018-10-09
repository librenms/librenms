<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
if ($device['os'] == 'dcn-software') {
    $usage = snmp_get($device, 'switchMemoryUsage.0', '-OvQ', 'DCN-MIB');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'dcn-software', 'Memory Usage', '1', null, null);
    }
}
