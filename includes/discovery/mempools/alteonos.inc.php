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
if ($device['os'] == 'alteonos') {
    $memutil = trim(snmp_get($device, '.1.3.6.1.4.1.1872.2.5.1.2.8.1.0', '-OvQ'));
    if (is_numeric($memutil)) {
        discover_mempool($valid_mempool, $device, 0, 'alteonos', 'Main Memory', '1', null, null);
    }
}
