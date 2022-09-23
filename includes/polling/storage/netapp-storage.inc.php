<?php

if (! is_array($storage_cache['netapp-storage'])) {
    $storage_cache['netapp-storage'] = snmpwalk_cache_oid($device, 'dfEntry', null, 'NETAPP-MIB');
    d_echo($storage_cache);
}

$entry = $storage_cache['netapp-storage'][$storage['storage_index']];

$storage['units'] = 1024;
if (isset($entry['df64TotalKBytes']) && is_numeric($entry['df64TotalKBytes'])) {
    $storage['used'] = ($entry['df64UsedKBytes'] * $storage['units']);
    $storage['size'] = ($entry['df64TotalKBytes'] * $storage['units']);
} else {
    $storage['used'] = ($entry['dfKBytesUsed'] * $storage['units']);
    $storage['size'] = ($entry['dfKBytesTotal'] * $storage['units']);
}

$storage['free'] = ($storage['size'] - $storage['used']);
