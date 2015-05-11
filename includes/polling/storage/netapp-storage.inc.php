<?php

if (!is_array($storage_cache['netapp-storage'])) {
    $storage_cache['hrstorage'] = snmpwalk_cache_oid($device, "dfEntry", NULL, "NETAPP-MIB");
    if ($debug) {
        print_r($storage_cache);
    }
}

$entry = $storage_cache['netapp-storage'][$storage[storage_index]];

$storage['units'] = 1024;
$storage['used'] = $entry['hrStorageUsed'] * $storage['units'];
$storage['size'] = $entry['hrStorageSize'] * $storage['units'];
$storage['free'] = $storage['size'] - $storage['used'];
