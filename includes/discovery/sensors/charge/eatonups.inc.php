<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

//xupsBatCapacity - XUPS-MIB
$charge_oid = '.1.3.6.1.4.1.534.1.2.4.0';
$charge = snmp_get($device, $charge_oid, '-Osqnv');

if (! empty($charge)) {
    $type = 'eatonups';
    $index = 0;
    $limit = 100;
    $lowlimit = 0;
    $lowwarnlimit = 10;
    $descr = 'Battery Charge';

    discover_sensor(
        $valid['sensor'],
        'charge',
        $device,
        $charge_oid,
        $index,
        $type,
        $descr,
        1,
        1,
        $lowlimit,
        $lowwarnlimit,
        null,
        $limit,
        $charge
    );
}
