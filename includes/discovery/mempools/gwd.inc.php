<?php
/*
 * LibreNMS GWD memory information module
 *
 * Copyright (c) 2019 hartred <tumanov@asarta.ru>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] === 'gwd') {
    echo 'gwd:';
    $usage = snmp_get($device, '.1.3.6.1.4.1.10072.2.20.1.1.2.1.1.20.1.1', '-Ovq');
    if (is_numeric($usage)) {
        $descr = 'Memory';
        discover_mempool($valid_mempool, $device, 0, 'gwd', $descr, '1', null, null);
    }
}
