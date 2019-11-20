<?php

if ($device['os'] === 'mypoweros') {
    echo 'MAIPU-MEMORY-POOL: ';
    $usage = snmp_get($device, 'allocBytesPercent.0', '-OvQ', 'MPIOS-MIB');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'mypoweros', 'Memory', '100', null, null);
    }
}
