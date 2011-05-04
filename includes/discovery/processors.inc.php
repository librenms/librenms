<?php

echo("Processors : ");

include_dir("includes/discovery/processors");

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
  }
  unset($processor_oid); unset($processor_type);
}

echo("\n");

?>
