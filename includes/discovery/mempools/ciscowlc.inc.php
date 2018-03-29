<?php

if ($device['os'] == 'ciscowlc') {
    echo 'Cisco WLC';

    $total = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.14179.1.1.5.3.0", '-OvQ'));
    $avail = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.14179.1.1.5.2.0", '-OvQ'));

    if ((is_numeric($total)) && (is_numeric($avail))) {
        discover_mempool($valid_mempool, $device, 0, 'ciscowlc', 'Memory', '1', null, null);
    }
}
