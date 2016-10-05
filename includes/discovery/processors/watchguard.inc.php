<?php

//
// Hardcoded discovery of cpu usage on WatchGuard devices.
//
// WATCHGUARD-SYSTEM-STATISTICS-MIB::wgSystemCpuUtil5.0 = COUNTER: 123
if ($device['os'] == 'firebox') {
    echo 'Watchguard Firebox : ';

    $descr = 'Processor';
    $usage = snmp_get($device, '.1.3.6.1.4.1.3097.6.3.78.0', '-OQUvs', 'WATCHGUARD-SYSTEM-STATISTICS-MIB', 'watchguard');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '1.3.6.1.4.1.3097.6.3.78.0', '0', 'firebox-fixed', $descr, '100', $usage, null, null);
    }
}

unset($processors_array);
