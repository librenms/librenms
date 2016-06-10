<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Steve Calvário <https://github.com/Calvario/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'eatonups') {
    echo 'XUPS-MIB';

    $load_oid = ".1.3.6.1.4.1.534.1.4.1.0";
    $descr    = 'Output Load';

    $load = snmp_get($device, $load_oid, '-OsqnU');

    $type     = 'xups';
    $index    = (100 + $i);

    discover_sensor($valid['sensor'], 'load', $device, $load_oid, $index, $type, $descr, '1', '1', null, null, null, null, $load);

}

