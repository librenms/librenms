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

if ($device['os'] == 'eatonsups') {
    echo 'Eaton UPS';

    // XUPS-MIB::xupsOutputNumPhases.0 = INTEGER: 1
    $oids = trim(snmp_walk($device, '.1.3.6.1.4.1.534.1.4.4.1.1', '-OsqnU'));
    d_echo($oids."\n");
    list($unused,$numPhase) = explode(' ', $oids);
	
	// XUPS-MIB::xupsOutputNumPhases.0 = INTEGER: 1
    $oids = trim(snmp_walk($device, '.1.3.6.1.4.1.534.1.4.4.1.1', '-OsqnU'));
    d_echo($oids."\n");
    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        // XUPS-MIB::xupsOutputVoltage.1 = INTEGER: 228
        $watts_oid = ".1.3.6.1.4.1.534.1.4.4.1.4.$i";
        $descr    = 'Output Watts';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }
        $type    = 'xups';
        $divisor = 1;
        $power = (snmp_get($device, $watts_oid, '-Oqv') / $divisor);
        $index   = '4.4.1.4.'.$i;
        discover_sensor($valid['sensor'], 'power', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $power);
    }
	
	
}
