<?php

if ($device['os'] == 'xos') {
    echo 'EXTREME-SOFTWARE-MONITOR-MIB';

    $total = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.1916.1.32.2.2.1.2.1", '-OvQ'));
    $avail = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.1916.1.32.2.2.1.3.1", '-OvQ'));

    if ((is_numeric($total)) && (is_numeric($avail))) {
        discover_mempool($valid_mempool, $device, 0, 'extreme-mem', 'Dynamic Memory', '1', null, null);
    }
}
