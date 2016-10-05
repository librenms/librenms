<?php

//
// Hardcoded discovery of cpu usage on RADLAN devices.
//
if ($device['os'] == 'radlan') {
    echo 'RADLAN : ';

    $descr = 'Processor';
    $usage = snmp_get($device, '.1.3.6.1.4.1.89.1.9.0', '-OQUvs', 'RADLAN-rndMng', 'radlan');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.89.1.9.0', '0', 'radlan', $descr, '1', $usage, null, null);
    }
}

unset($processors_array);
