<?php

// HOST-RESOURCES-MIB - Memory Objects
if (! is_array($storage_cache['hrstorage'])) {
    $storage_cache['hrstorage'] = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');
    $storage_cache['memorySize'] = snmp_get($device, 'hrMemorySize.0', '-OUqv', 'HOST-RESOURCES-MIB');
    $storage_cache['memorySize'] = $storage_cache['memorySize'] * 1024; // hrMemorySize is stored in KBytes
    d_echo($storage_cache);
} else {
    d_echo('Cached!');
}

$entry = $storage_cache['hrstorage'][$mempool['mempool_index']];
$mempool['units'] = $entry['hrStorageAllocationUnits'];

if ($mempool['mempool_index'] == 1 && isset($storage_cache['hrstorage'][11])) {  // Phisical memory && sysAvail field exists
    $availEntry = $storage_cache['hrstorage'][11];
    $mempool['free'] = $availEntry['hrStorageSize'] * $mempool['units'];
} else {
    $mempool['used'] = ($entry['hrStorageUsed'] * $mempool['units']);
}

if ($device['sysObjectID'] == '.1.3.6.1.4.1.12325.1.1.2.1.1') { // bsnmpd based devices, like FreeBSD, opnsense, pfsense ...
    $mempool['total'] = $storage_cache['memorySize'];
} else {
    $mempool['total'] = ($entry['hrStorageSize'] * $mempool['units']);
}

if (array_key_exists('free', $mempool)) {
    $mempool['used'] = $mempool['total'] - $mempool['free'];
} else {
    $mempool['free'] = $mempool['total'] - $mempool['used'];
}
