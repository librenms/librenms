<?php

if ($device['os'] == 'f5') {
    echo 'F5: ';

    $total   = snmp_get($device, 'sysGlobalStat.sysStatMemoryTotal.0', '-OvQ', 'F5-BIGIP-SYSTEM-MIB', 'f5');
    $used = snmp_get($device, 'sysGlobalStat.sysStatMemoryUsed.0', '-OvQ', 'F5-BIGIP-SYSTEM-MIB', 'f5');
    $percent    = ($total / $used) * 100;
    $free    = ($total - $used);
    if (is_numeric($total) && is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, 'f5', 'TMM Memory', '1', null, null);
    }
}
