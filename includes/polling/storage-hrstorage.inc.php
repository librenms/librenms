<?php

/// HOST-RESOURCES-MIB - Storage Objects

if(!is_array($storage_cache['hrstorage'])) {
  $storage_cache['hrstorage'] = snmp_cache_oid("hrStorageEntry", $device, array(), "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES");
  if ($debug) { print_r($storage_cache); }
}

$entry = $storage_cache['hrstorage'][$device[device_id]][$storage[storage_index]];

$storage['units'] = $entry['hrStorageAllocationUnits'];
$storage['used'] = $entry['hrStorageUsed'] * $storage['units'];
$storage['size'] = $entry['hrStorageSize'] * $storage['units'];
$storage['free'] = $storage['size'] - $storage['used'];

?>
