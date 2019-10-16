<?php

if ($device['os'] == 'snr') {
    $usage = snmp_get($device, 'sysMemoryUsage.1', '-OvQ', 'NAG-MIB');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'snr', 'Memory Usage', '1', null, null);
    }
}
