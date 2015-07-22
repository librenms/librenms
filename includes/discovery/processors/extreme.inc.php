<?php

//
// Hardcoded discovery of CPU usage on Extreme devices.
//
// iso.3.6.1.4.1.1916.1.32.1.4.1.9.1 = STRING: "7.3"
if ($device['os'] == 'xos') {
    echo 'EXTREME-BASE-MIB';

    $descr = 'Processor';
    $usage = str_replace('"', "", snmp_get($device, '1.3.6.1.4.1.1916.1.32.1.4.1.9.1', '-OvQ', 'EXTREME-BASE-MIB'));

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '1.3.6.1.4.1.1916.1.32.1.4.1.9.1', '0', 'extreme-cpu', $descr, '100', $usage, null, null);
    }
}

unset($processors_array);
