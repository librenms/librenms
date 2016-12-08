<?php

// HOST-RESOURCES-MIB - Memory Objects
if (!is_array($storage_cache['hrstorage'])) {
    $storage_cache['hrstorage'] = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');
    d_echo($storage_cache);
} else {
    d_echo('Cached!');
}

$entry = $storage_cache['hrstorage'][$mempool[mempool_index]];

$mempool['units'] = $entry['hrStorageAllocationUnits'];
$mempool['used']  = ($entry['hrStorageUsed'] * $mempool['units']);
$mempool['total'] = ($entry['hrStorageSize'] * $mempool['units']);
$mempool['free']  = ($mempool['total'] - $mempool['used']);
