<?php

echo 'FORTISANDBOX-MEMORY-POOL: ';
$usage = str_replace('"', '', snmp_get($device, 'FORTINET-FORTISANDBOX-MIB::fsaSysMemUsage.0', '-OvQ'));
if (is_numeric($usage)) {
    // fsaSysMemCapacity: Total physical memory (RAM) installed (KB) - FortiSandbox uses Base 10 - Precision = 1000
    discover_mempool($valid_mempool, $device, 0, 'fortisandbox', 'Physical Memory', '1000', null, null);
}
