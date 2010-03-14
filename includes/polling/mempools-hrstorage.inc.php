<?php

/// HOST-RESOURCES-MIB - Memory Objects

if(!is_array($storage_cache['hrstorage'])) {
  $storage_cache['hrstorage'] = snmp_cache_oid("hrStorageEntry", $device, array(), "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES");
  if ($debug) { print_r($storage_cache); }
} else { if($debug) { echo("Cached!"); } }

$entry = $storage_cache['hrstorage'][$device[device_id]][$mempool[index]];

$mempool['units'] = $entry['hrStorageAllocationUnits'];
$mempool['used'] = $entry['hrStorageUsed'] * $mempool['units'];
$mempool['total'] = $entry['hrStorageSize'] * $mempool['units'];
$mempool['free'] = $mempool['total'] - $mempool['used'];

?>
