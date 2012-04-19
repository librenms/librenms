<?php

$hrstorage_array = snmpwalk_cache_oid($device, "hrStorageEntry", NULL, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES:NetWare-Host-Ext-MIB");

if (is_array($hrstorage_array))
{
  echo("hrStorage : ");
  foreach ($hrstorage_array as $index => $storage)
  {
    $fstype = $storage['hrStorageType'];
    $descr = $storage['hrStorageDescr'];
    $size = $storage['hrStorageSize'] * $storage['hrStorageAllocationUnits'];
    $used = $storage['hrStorageUsed'] * $storage['hrStorageAllocationUnits'];
    $units = $storage['hrStorageAllocationUnits'];

    switch($fstype)
    {
      case 'hrStorageVirtualMemory':
      case 'hrStorageRam';
      case 'hrStorageOther';
      case 'nwhrStorageDOSMemory';
      case 'nwhrStorageMemoryAlloc';
      case 'nwhrStorageMemoryPermanent';
      case 'nwhrStorageMemoryAlloc';
      case 'nwhrStorageCacheBuffers';
      case 'nwhrStorageCacheMovable';
      case 'nwhrStorageCacheNonMovable';
      case 'nwhrStorageCodeAndDataMemory';
      case 'nwhrStorageDOSMemory';
      case 'nwhrStorageIOEngineMemory';
      case 'nwhrStorageMSEngineMemory';
      case 'nwhrStorageUnclaimedMemory';
        $deny = 1;
        break;
    }

    foreach ($config['ignore_mount'] as $bi) { if ($bi == $descr) { $deny = 1; if ($debug) echo("$bi == $descr \n"); } }
    foreach ($config['ignore_mount_string'] as $bi) { if (strpos($descr, $bi) !== FALSE)     { $deny = 1; if ($debug) echo("strpos: $descr, $bi \n"); } }
    foreach ($config['ignore_mount_regexp'] as $bi) { if (preg_match($bi, $descr) > "0") { $deny = 1; if ($debug) echo("preg_match $bi, $descr \n"); } }

    if (isset($config['ignore_mount_removable']) && $config['ignore_mount_removable'] && $fstype == "hrStorageRemovableDisk") { $deny = 1; if ($debug) echo("skip(removable)\n"); }
    if (isset($config['ignore_mount_network']) && $config['ignore_mount_network'] && $fstype == "hrStorageNetworkDisk") { $deny = 1; if ($debug) echo("skip(network)\n"); }
    if (isset($config['ignore_mount_optical']) && $config['ignore_mount_optical'] && $fstype == "hrStorageCompactDisc") { $deny = 1; if ($debug) echo("skip(cd)\n"); }

    if (!$deny && is_numeric($index))
    {
      discover_storage($valid_storage, $device, $index, $fstype, "hrstorage", $descr, $size , $units, $used);
    }

    #$old_storage_rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("hrStorage-" . $index . ".rrd");
    #$storage_rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("storage-hrstorage-" . $index . ".rrd");
    #if (is_file($old_storage_rrd)) { rename($old_storage_rrd,$storage_rrd); }

    unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);

  }
}

?>
