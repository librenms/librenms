<?php
if (!is_array($storage_cache['nimbleos'])) {
    $storage_cache['nimbleos'] = snmpwalk_cache_oid($device, 'volEntry', null, 'NIMBLE-MIB');
    d_echo($storage_cache);
}
$entry = $storage_cache['nimbleos'][$storage[storage_index]];
$storage['units'] = 1024*1024;
$storage['size'] = ($entry['volSizeLow'] * $storage['units']);
$storage['used'] = ($entry['volUsageLow'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
