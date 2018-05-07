<?php

if ($device['os'] === 'netspire') {
    echo 'Netspire Memory Pool\n';
    $usage = str_replace('"', "", snmp_get($device, '1.3.6.1.4.1.1732.2.1.8.0', "-OQv"));

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'netspire', 'Storage');
    }
}
