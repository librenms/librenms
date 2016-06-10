<?php

// MGE UPS Voltages
if ($device['os'] == 'mgeups') {
    echo 'MGE ';

    // XUPS-MIB::xupsOutputNumPhases.0 = INTEGER: 1
    $oids = trim(snmp_walk($device, '.1.3.6.1.4.1.705.1.7.1.0', '-OsqnU'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        $load_oid = ".1.3.6.1.4.1.705.1.7.2.1.4.$i";
        $descr    = 'Output Load';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $current = snmp_get($device, $load_oid, '-Oqv');
        if (!$current) {
            $load_oid .= '.0';
            $Phaseload   = snmp_get($device, $load_oid, '-Oqv');
        }

        $type     = 'mge-ups';
        $index    = (100 + $i);

        discover_sensor($valid['sensor'], 'load', $device, $load_oid, $index, $type, $descr, '1', '1', null, null, null, null, $load);
    }

}//end if
