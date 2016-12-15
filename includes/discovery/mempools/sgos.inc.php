<?php
//
// Hardcoded discovery of Memory usage on SGOS
//

if ($device['os'] == 'sgos') {
    echo 'ProxySG-Mem-Pool: ';

    $used = snmp_get($device, 'BLUECOAT-SG-PROXY-MIB::sgProxyMemSysUsage.0', '-OQUv');
    $total = snmp_get($device, 'BLUECOAT-SG-PROXY-MIB::sgProxyMemAvailable.0', '-OQUv');

    if (is_numeric($used) && is_numeric($total)) {
        discover_mempool($valid_mempool, $device, 0, 'sgos', 'ProxySG Memory', '1', null, null);
    }
}
