<?php

// This isn't a volume, so we have to fake it a bit
if ($device['os'] === 'primekey-sw') {
    $oids = snmp_get_multi_oid($device, ['pk-SAV2-internal-databaseAvailableStorage.0', 'pk-SAV2-internal-databaseTotalStorage.0'], '-OUQn', 'PK-SOFTWARE-APPLIANCE-V2');

    $fstype = 'sql';
    $descr = 'Internal Database';
    $units = 1024;
    $index = 0;
    $free = $oids['.1.3.6.1.4.1.22408.1.4.1.3.1.2.0'];
    $total = $oids['.1.3.6.1.4.1.22408.1.4.1.3.1.3.0'];
    $used = $total - $free;
    if (is_numeric($free) && is_numeric($total)) {
        discover_storage($valid_storage, $device, $index, $fstype, 'primekey-sw-sql', $descr, $total, $units, $used);
    }
    unset($oids);
}
