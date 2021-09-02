<?php

if (! is_array($storage_cache['zpool'])) {
    $storage_cache['zpool'] = snmpwalk_cache_oid($device, 'zpoolTable', null, 'FREENAS-MIB');
    d_echo($storage_cache);
}

$entry = $storage_cache['zpool'][$storage['storage_index']];

$storage['units'] = $entry['zpoolAllocationUnits'];
$storage['size'] = ($entry['zpoolSize'] * $storage['units']);
$storage['free'] = ($entry['zpoolAvailable'] * $storage['units']);
$storage['used'] = ($storage['size'] - $storage['free']);
