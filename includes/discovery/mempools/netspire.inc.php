<?php

if ($device['os'] === 'netspire') {
    echo 'Netspire Memory Pool\n';
    $usage = str_replace('"', "", snmp_get($device, 'netSpireDeviceStorageUsed.0', "-OQv", 'OACOMMON-MIB'));

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'netspire', 'Storage');
    }
}
