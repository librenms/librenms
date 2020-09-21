<?php

echo 'FORTIAUTHENTICATOR-MEMORY-POOL: ';
$usage = str_replace('"', '', snmp_get($device, 'FORTINET-FORTIAUTHENTICATOR-MIB::facSysMemUsage.0', '-OvQ'));
if (is_numeric($usage)) {
    // get hardware memory: Total physical memory (RAM) installed (KB) - FortiAuthenticator uses Base 2 - Precision = 1024
    discover_mempool($valid_mempool, $device, 0, 'fortiauthenticator', 'Physical Memory', '1024', null, null);
}
