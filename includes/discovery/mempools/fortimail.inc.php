<?php

if ($device['os'] == 'fortimail') {
    echo 'FORTIMAIL-MEMORY-POOL: ';
    $usage = str_replace('"', '', snmp_get($device, 'FORTINET-FORTIMAIL-MIB::fmlSysMemUsage.0', '-OvQ'));
    if (is_numeric($usage)) {
        // fmlSysMemCapacity: Total physical memory (RAM) installed (KB)
        // Conversion from kilobyte (base 2) to byte (base 10): Precision = 1024
        discover_mempool($valid_mempool, $device, 0, 'fortimail', 'Physical Memory', '1024', null, null);
    }
}
