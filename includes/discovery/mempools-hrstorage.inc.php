<?php

$storage_array = snmpwalk_cache_oid($device, "hrStorageEntry", NULL, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES");

if(is_array($storage_array)) {
  echo("hrStorage : ");
  foreach($storage_array[$device[device_id]] as $index => $storage) 
  {
    $fstype = $storage['hrStorageType'];
    $descr  = $storage['hrStorageDescr'];
    $size   = $storage['hrStorageSize'] * $storage['hrStorageAllocationUnits'];
    $used   = $storage['hrStorageUsed'] * $storage['hrStorageAllocationUnits'];
    $units  = $storage['hrStorageAllocationUnits'];
    $deny   = 1;

    if ($fstype == "hrStorageVirtualMemory" || $fstype == "hrStorageRam") { $deny = 0; }
    if ($device['os'] == "routeros" && $descr == "main memory") { $deny = 0; }

    if(strstr($descr, "MALLOC") || strstr($descr, "UMA")) { $deny = 1;  } ## Ignore FreeBSD INSANITY

    if(!$deny && is_numeric($index)) {
      discover_mempool($valid_mempool, $device, $index, "hrstorage", $descr, $units, NULL, NULL);
    }
    unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $storage_array);
  }
}

?>
