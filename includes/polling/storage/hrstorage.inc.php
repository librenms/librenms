<?php

use Illuminate\Support\Facades\Log;

// HOST-RESOURCES-MIB - Storage Objects
if (! isset($storage_cache['hrstorage'])) {
    $storage_cache['hrstorage'] = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');
    d_echo($storage_cache);
}

// find storage entry
$entry = $storage_cache['hrstorage'][$storage['storage_index']] ?? null;

if (($entry['hrStorageDescr'] ?? null) !== $storage['storage_descr']) {
    // couldn't find a match, check by storage_descr or fall back to data found by index in case descr changed
    $entry = collect($storage_cache['hrstorage'])->firstWhere('hrStorageDescr', $storage['storage_descr']) ?? $entry;
}

if (empty($entry)) {
    Log::error("%rCould not find storage data for {$storage['storage_descr']}%n", ['color' => true]);
    // keep last known values
    $storage['units'] = $storage['storage_units'];
    $storage['used'] = $storage['storage_used'];
    $storage['size'] = $storage['storage_size'];
    $storage['free'] = $storage['storage_free'];

    return;
}

if ($entry['hrStorageIndex'] !== $storage['storage_index']) {
    Log::debug("Storage {$storage['storage_descr']} changed index {$storage['storage_index']} > {$entry['hrStorageIndex']}, applying quickfix until discovery runs");
}

$storage['units'] = $entry['hrStorageAllocationUnits'];
$entry['hrStorageUsed'] = fix_integer_value($entry['hrStorageUsed'] ?? 0);
$entry['hrStorageSize'] = fix_integer_value($entry['hrStorageSize']);
$storage['used'] = ($entry['hrStorageUsed'] * $storage['units']);
$storage['size'] = ($entry['hrStorageSize'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
