<?php

if ($device['os'] === 'ionodes') {
    echo 'IONODES IONSERIES';

    $usage = snmp_get($device, 'ionSysMemUsage.0', '-OvUQ', 'IONODES-IONSERIES-MIB');

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'ionodes', 'System Memory', '100', null, null);
    }
}
