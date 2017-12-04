<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Martin Zatloukal <slezi2@pvfree.net> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'edgecos') {
    echo 'Edgecos : ';

    if (!empty(snmp_get($device, '.1.3.6.1.4.1.259.10.1.24.1.39.2.1.0', '-Ovqn'))) { //ECS4510
        $oid = '.1.3.6.1.4.1.259.10.1.24.1.39.2.1.0';
    } elseif (!empty(snmp_get($device, '.1.3.6.1.4.1.259.10.1.22.1.39.2.1.0', '-Ovqn'))) { //ECS3528
        $oid = '.1.3.6.1.4.1.259.10.1.22.1.39.2.1.0';
    } elseif (!empty(snmp_get($device, '.1.3.6.1.4.1.259.10.1.45.1.39.2.1.0', '-Ovqn'))) { //ECS4120
        $oid = '.1.3.6.1.4.1.259.10.1.45.1.39.2.1.0';
    } elseif (!empty(snmp_get($device, '.1.3.6.1.4.1.259.10.1.42.101.1.39.2.1.0', '-Ovqn'))) { //ECS4210
        $oid = '.1.3.6.1.4.1.259.10.1.42.101.1.39.2.1.0';
    } else {
        $oid = '.1.3.6.1.4.1.259.10.1.27.1.39.2.1.0';
    };

    $usage = snmp_get($device, $oid, '-Ovqn');
    $descr = 'Processor';
    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, $oid, '0', 'edgecos', $descr, '1', $usage);
    }
}
