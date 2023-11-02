<?php

if (! isset($storage_cache['zpool'])) {
    $storage_cache['zpool'] = snmpwalk_cache_oid($device, 'zpoolUsed', null, 'FREENAS-MIB');
    d_echo($storage_cache);
}

if (isset($storage_cache['zpool'][$storage['storage_index']]['zpoolUsed'])) {
    $used = $storage_cache['zpool'][$storage['storage_index']]['zpoolUsed'];

    $storage['used'] = $used * $storage['storage_units'];
    $storage['free'] = $storage['storage_size'] - $storage['used'];

    // leave these the same
    $storage['size'] = $storage['storage_size'];
    $storage['units'] = $storage['storage_units'];
}
