<?php

// HOST-RESOURCES-MIB - Storage Objects
if (! isset($storage_cache['hrstorage'])) {
    $storage_cache['hrstorage'] = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');
    d_echo($storage_cache);
}

$entry = $storage_cache['hrstorage'][$storage['storage_index']];

/*
 * It is possible that the HOST-RESOURCES-MIB::hrStorageTable has updated between
 * discovery and polling.    If we assume it hasn't we can end up assigning the
 * values of the wrong mount point to another mount point.   This can cause
 * issues with not only display, but also alarming.
 *
 * We can compare the description expected with one from the hrStorageIndex entry
 * and if they are not equal then we can filter the table to return an entry
 * that does match.
 *
 * (example of how to debug --> ./poller.php -h 42 -r -f -m storage -d)
 *
 */

if( $entry['hrStorageDescr'] !== $descr ){
    d_echo( '🚨 We are after [' . $descr . '] but hrStorageIndex entry has [' . $entry['hrStorageDescr'] . '] ' );
    d_echo( 'Before: ' . var_export( $entry, true ));
    $entry = array_values( array_filter( $storage_cache['hrstorage'], function($entry) use ($descr) {
        return $entry['hrStorageDescr'] === $descr;
    }))[0];
    d_echo( 'After: ' . var_export( $entry, true ) );
    d_echo( '🚨 updated to [' . $entry['hrStorageDescr'] . ']' );
}


$storage['units'] = $entry['hrStorageAllocationUnits'];
$entry['hrStorageUsed'] = fix_integer_value($entry['hrStorageUsed'] ?? 0);
$entry['hrStorageSize'] = fix_integer_value($entry['hrStorageSize']);
$storage['used'] = ($entry['hrStorageUsed'] * $storage['units']);
$storage['size'] = ($entry['hrStorageSize'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
