<?php

echo("Storage : ");

// Include all discovery modules

$include_dir = "includes/discovery/storage";
include("includes/include-dir.inc.php");

// Remove storage which weren't redetected here

$sql = "SELECT * FROM `storage` WHERE `device_id`  = '".$device['device_id']."'";

if ($debug) { print_r ($valid_storage); }

foreach (dbFetchRows($sql) as $test_storage)
{
  $storage_index = $test_storage['storage_index'];
  $storage_mib = $test_storage['storage_mib'];
  if ($debug) { echo($storage_index . " -> " . $storage_mib . "\n"); }

  if (!$valid_storage[$storage_mib][$storage_index])
  {
    echo("-");
    dbDelete('storage', '`storage_id` = ?', array($test_storage['storage_id']));
  }

  unset($storage_index); unset($storage_mib);
}

unset($valid_storage);
echo("\n");

?>
