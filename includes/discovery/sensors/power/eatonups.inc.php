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

        // XUPS-MIB::xupsOutputWatts.1 = INTEGER: 228
        $watts_oid = ".1.3.6.1.4.1.534.1.4.4.1.4.1";
        $descr    = 'Output Watts';

        $type    = 'xups';
        $divisor = 1;
        $power = (snmp_get($device, $watts_oid, '-Oqv') / $divisor);
        $index   = 100+$i;

        discover_sensor($valid['sensor'], 'power', $device, $watt_oid, $index, $type, $descr, '1', '1', null, null, null, null, $power);
}
