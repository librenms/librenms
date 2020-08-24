<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2020 hartred <tumanov@asarta.ru>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'bdcom') {
    $used = snmp_get($device, '.1.3.6.1.4.1.3320.3.6.10.1.12.0', '-OvQ');
    if (is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, 'bdcom', 'Memory', '1', null, null);
    }
}
