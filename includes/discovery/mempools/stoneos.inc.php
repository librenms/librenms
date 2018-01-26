<?php
if ($device['os'] === 'stoneos') {
    $currentMemory = snmp_get($device, 'HILLSTONE-SYSTEM-MIB::sysCurMemory.0', '-OvQU');
    if (is_numeric($currentMemory)) {
        discover_mempool($valid_mempool, $device, 0, 'stoneos', 'Memory', '1', null, null);
    }
}
