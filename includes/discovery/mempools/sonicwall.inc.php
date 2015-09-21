<?php

if ($device['os'] == 'sonicwall') {
    echo 'SonicWALL-MEMORY-POOL: ';
    $usage = snmp_get($device, 'SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicCurrentRAMUtil.0', '-Ovq');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'sonicwall-mem', 'Memory Utilization', '100', null, null);
    }
}
