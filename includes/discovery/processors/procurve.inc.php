<?php

//
// Hardcoded discovery of cpu usage on HP Procurve devices.
//
// STATISTICS-MIB::hpSwitchCpuStat.0 = INTEGER: 10
if ($device['os'] == 'procurve') {
    echo 'Procurve : ';

    $descr = 'Processor';
    $usage = snmp_get($device, '.1.3.6.1.4.1.11.2.14.11.5.1.9.6.1.0', '-OQUvs', 'STATISTICS-MIB', 'hp');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '1.3.6.1.4.1.11.2.14.11.5.1.9.6.1.0', '0', 'procurve-fixed', $descr, '1', $usage, null, null);
    }
}

unset($processors_array);
