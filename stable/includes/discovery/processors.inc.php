<?php

echo("Processors : ");

### Include all discovery modules

$include_dir = "includes/discovery/processors";
include("includes/include-dir.inc.php");

## Last-resort discovery here

include("processors-ucd-old.inc.php");

### Remove processors which weren't redetected here

$sql = "SELECT * FROM `processors` WHERE `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

if ($debug) { print_r ($valid['processor']); }

while ($test_processor = mysql_fetch_assoc($query))
{
  $processor_index = $test_processor['processor_index'];
  $processor_type = $test_processor['processor_type'];
  if ($debug) { echo($processor_index . " -> " . $processor_type . "\n"); }
  if (!$valid['processor'][$processor_type][$processor_index])
  {
    echo("-");
    mysql_query("DELETE FROM `processors` WHERE processor_id = '" . $test_processor['processor_id'] . "'");
    log_event("Processor removed: type ".$processor_type." index ".$processor_index." descr ". $test_processor['processor_descr'], $device, 'processor', $test_processor['processor_id']);
  }
  unset($processor_oid); unset($processor_type);
}

echo("\n");

?>
