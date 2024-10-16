<?php

if (! isset($storage_cache['zvol'])) {
    $storage_cache['zvol'] = snmpwalk_cache_oid($device, 'zvolUsedBytes', null, 'TRUENAS-MIB');
    d_echo($storage_cache);
}
if (isset($storage_cache['zvol'][$storage['storage_index']]['zvolUsedBytes'])) {
    $used = $storage_cache['zvol'][$storage['storage_index']]['zvolUsedBytes'];
    // expected variables for the storage polling module
    $storage['used'] = $used;
    $storage['free'] = $storage['storage_size'] - $storage['used'];
    $storage['size'] = $storage['storage_size'];
    $storage['units'] = $storage['storage_units'];
}
