<?php
if ($device['os'] === 'stoneos') {
    $currentMemory = snmp_get($device, 'sysCurMemory.0', '-OvQU', 'HILLSTONE-SYSTEM-MIB');
    if (is_numeric($currentMemory)) {
        discover_mempool($valid_mempool, $device, 0, 'stoneos', 'Memory', '1', null, null);
    }
}
