<?php

if ($device['os'] == 'fortiweb') {
    echo 'FORTIWEB-MEMORY-POOL: ';
    $usage = str_replace('"', '', snmp_get($device, 'FORTINET-FORTIWEB-MIB::fwSysMemUsage.0', '-OvQ'));
    if (is_numeric($usage)) {
        // fwSysMemCapacity: Total physical memory (RAM) installed (MB)
        // Conversion from megabyte (base 10) to byte (base 10): Precision = 1000*1000
        discover_mempool($valid_mempool, $device, 0, 'fortiweb', 'Physical Memory', '1000000', null, null);
    }
}
