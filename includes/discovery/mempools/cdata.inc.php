<?php
/*
 * LibreNMS Cdata memory information module
 *
 * Copyright (c) 2019 hartred <tumanov@asarta.ru>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'cdata') {
    echo 'CDATA-MEMORY-POOL: ';

    $total = snmp_get($device, '.1.3.6.1.4.1.34592.1.3.100.1.8.2.0', '-OvQ');
    $free  = snmp_get($device, '.1.3.6.1.4.1.34592.1.3.100.1.8.3.0', '-OvQ');

    if (is_numeric($total) && is_numeric($free)) {
        discover_mempool($valid_mempool, $device, 0, 'cdata', 'Memory', '1', null, null);
    }
}
