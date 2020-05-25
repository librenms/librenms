<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2019 hartred <tumanov@asarta.ru>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'snr') {
    $usage = snmp_get($device, 'sysMemoryUsage.1', '-OvQ', 'NAG-MIB');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'snr', 'Memory Usage', '1', null, null);
    }
}
