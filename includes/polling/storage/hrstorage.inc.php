<?php

// HOST-RESOURCES-MIB - Storage Objects
if (! is_array($storage_cache['hrstorage'])) {
    $storage_cache['hrstorage'] = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');
    d_echo($storage_cache);
}

$entry = $storage_cache['hrstorage'][$storage['storage_index']];

$storage['units'] = $entry['hrStorageAllocationUnits'];
$entry['hrStorageUsed'] = fix_integer_value($entry['hrStorageUsed']);
$entry['hrStorageSize'] = fix_integer_value($entry['hrStorageSize']);
$storage['used'] = ($entry['hrStorageUsed'] * $storage['units']);
$storage['size'] = ($entry['hrStorageSize'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
