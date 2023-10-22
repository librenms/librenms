<?php

if (! isset($storage_cache['zpool'])) {
    $storage_cache['zpool'] = snmpwalk_cache_oid($device, 'zpoolUsed', null, 'FREENAS-MIB');
    d_echo($storage_cache);
}

if (isset($storage_cache['zpool'][$storage['storage_index']]['zpoolUsed'])) {
    $used = $storage_cache['zpool'][$storage['storage_index']]['zpoolUsed'];

    $storage['used'] = $used * $storage['units'];
    $storage['free'] = $storage['size'] - $storage['used'];
}
