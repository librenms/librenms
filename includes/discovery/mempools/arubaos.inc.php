<?php

if ($device['os'] === 'arubaos') {
    echo 'ARUBAOS-MEMORY-POOL: ';

    $memory_pool = snmp_get_multi_oid($device, ['sysXMemorySize.1', 'sysXMemoryUsed.1', 'sysXMemoryFree.1'], '-OQUs', 'WLSX-SWITCH-MIB');
    
    $total = $memory_pool['sysXMemorySize.1'];
    $used  = $memory_pool['sysXMemoryUsed.1'];
    $free  = $memory_pool['sysXMemoryFree.1'];
    $perc  = ($mempool['used'] / $mempool['total'] * 100);

    if (is_numeric($total) && is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, 'arubaos', 'Memory', '1', null, null);
    }
}
