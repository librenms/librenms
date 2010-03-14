<?php

$storage_array = snmp_cache_oid("hrStorageEntry", $device, array(), "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES");

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

    if ($fstype == "hrStorageVirtualMemory" || $fstype == "hrStorageRam" || $fstype == "hrStorageOther") { $deny = 0; }

    if(!$deny && is_numeric($index)) {
      discover_mempool($valid_mempool, $device, $index, "hrstorage", $descr, $units, NULL, NULL);
    }
    unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $storage_array);
  }
}

?>
