<?php

if (! is_array($storage_cache['dataset'])) {
    $storage_cache['dataset'] = snmpwalk_cache_oid($device, 'datasetTable', null, 'FREENAS-MIB');
    d_echo($storage_cache);
}

foreach ($storage_cache['dataset'] as $index => $entry) {
    $storage['units'] = $entry['datasetAllocationUnits'];
    $storage['size'] = ($entry['datasetSize'] * $storage['units']);
    $storage['free'] = ($entry['datasetAvailable'] * $storage['units']);
    $storage['used'] = ($storage['size'] - $storage['free']);
}
