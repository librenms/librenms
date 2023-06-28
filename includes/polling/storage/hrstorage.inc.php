<?php

use Illuminate\Support\Facades\Log;

// HOST-RESOURCES-MIB - Storage Objects
if (! isset($storage_cache['hrstorage'])) {
    $storage_cache['hrstorage'] = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');
    d_echo($storage_cache);
}

// find storage entry
if (isset($storage_cache['hrstorage'][$storage['storage_index']]) && $storage_cache['hrstorage'][$storage['storage_index']]['hrStorageDescr'] == $storage['storage_descr']) {
    $entry = $storage_cache['hrstorage'][$storage['storage_index']];
} else {
    // couldn't find a match, check by storage_descr in case the index changed
    $entry = collect($storage_cache['hrstorage'])->firstWhere('hrStorageDescr', $storage['storage_descr']);

    if (empty($entry)) {
        Log::error('%rCould not find storage data for ' . $storage['storage_descr'] . '%n', ['color' => true]);
        // keep last known values
        $storage['units'] = $storage['storage_units'];
        $storage['used'] = $storage['storage_used'];
        $storage['size'] = $storage['storage_size'];
        $storage['free'] = $storage['storage_free'];

        return;
    }

    Log::debug("Storage changed index {$storage['storage_index']} > {$entry['hrStorageIndex']}, applying quickfix until discovery runs");
}

$storage['units'] = $entry['hrStorageAllocationUnits'];
$entry['hrStorageUsed'] = fix_integer_value($entry['hrStorageUsed'] ?? 0);
$entry['hrStorageSize'] = fix_integer_value($entry['hrStorageSize']);
$storage['used'] = ($entry['hrStorageUsed'] * $storage['units']);
$storage['size'] = ($entry['hrStorageSize'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
