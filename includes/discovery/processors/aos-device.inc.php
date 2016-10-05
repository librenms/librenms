<?php

// Hardcoded discovery of device CPU usage on Alcatel-Lucent Omniswitches.
if ($device['os'] == 'aos') {
    echo 'Alcatel-Lucent Device : ';

    $descr = 'Device CPU';
    $usage = snmp_get($device, '1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.13.0', '-OQUvs', 'ALCATEL-IND1-HEALTH-MIB', 'aos');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.13.0', '0', 'aos-system', $descr, '1', $usage, null, null);
    } else {
        // AOS7 devices use a different OID for CPU load. Not all Switches have
        // healthModuleCpuLatest so we use healthModuleCpu1MinAvg which makes no
        // difference for a 5 min. polling interval.
        // Note: This OID shows (a) the CPU load of a single switch or (b) the
        // average CPU load of all CPUs in a stack of switches.
        $usage = snmp_get($device, '1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.11.0', '-OQUvs', null, null);
        if (is_numeric($usage)) {
            discover_processor($valid['processor'], $device, '1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.11.0', '0', 'aos-system', $descr, '1', $usage, null, null);
        }
    }
}//end if
