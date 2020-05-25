<?php
if ($device['os'] == 'hirschmann') {
    $mem_allocated = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmMemoryAllocated.0', '-OvQ');
    $mem_free = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmMemoryFree.0', '-OvQ');
    $usage = $mem_allocated / ($mem_allocated + $mem_free);

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'hirschmann-mem', 'Main Memory', '100', null, null);
    }
}
