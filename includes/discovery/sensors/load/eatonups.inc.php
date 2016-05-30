<?php
// test comment

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
    echo 'Eaton UPS Load';

    // XUPS-MIB::xupsOutputNumPhases.0 = INTEGER: 1
    $oids = trim(snmp_walk($device, 'xupsOutputNumPhases', '-OsqnU'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);	
	
    // Eaton UPS Battery Load Level
    for ($i = 1; $i <= $numPhase; $i++) {
        $load_oid = ".1.3.6.1.4.1.534.1.4.1.0";
        $descr    = 'Output Load';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $current = snmp_get($device, $load_oid, '-Oqv');
        if (!$current) {
            $load_oid .= '.0';
            $Phaseload   = snmp_get($device, $load_oid, '-Oqv');
        }

//        $current /= 10;
        $type     = 'mge-ups';
//        $divisor  = 10;
        $index    = (100 + $i);

        discover_sensor($valid['sensor'], 'load', $device, $load_oid, $index, $type, $descr, '1', '1', null, null, null, null, $load);
    }

}
