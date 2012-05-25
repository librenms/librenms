<?php

echo("Storage : ");

/// Include all discovery modules

$include_dir = "includes/discovery/storage";
include("includes/include-dir.inc.php");

/// Remove storage which weren't redetected here

$sql = "SELECT * FROM `storage` WHERE `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

if ($debug) { print_r ($valid_storage); }

while ($test_storage = mysql_fetch_assoc($query))
{
  $storage_index = $test_storage['storage_index'];
  $storage_mib = $test_storage['storage_mib'];
  if ($debug) { echo($storage_index . " -> " . $storage_mib . "\n"); }

  if (!$valid_storage[$storage_mib][$storage_index])
  {
    echo("-");
    mysql_query("DELETE FROM `storage` WHERE storage_id = '" . $test_storage['storage_id'] . "'");
  }

  unset($storage_index); unset($storage_mib);
}

unset($valid_storage);
echo("\n");

?>
