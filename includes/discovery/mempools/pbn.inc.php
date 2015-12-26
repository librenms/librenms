<?php
if ($device['os'] == 'pbn') {
    echo 'PBN-MEMORY-POOL: ';

    $usage = snmp_get($device, 'NMS-MEMORY-POOL-MIB::nmsMemoryPoolUtilization.0', '-OvQ');

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'pbn-mem', 'Main Memory', '100', null, null);
    }
}
