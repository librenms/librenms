<?php

echo("Processors : ");

include("processors-ios.inc.php");
include("processors-junose.inc.php");
include("processors-hrdevice.inc.php");

### Remove processors which weren't redetected here

$sql = "SELECT * FROM `processors` WHERE `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

if($debug) { print_r ($valid_processor); }

while ($test_processor = mysql_fetch_array($query)) {
  $processor_index = $test_processor['processor_index'];
  $processor_type = $test_processor['processor_type'];
  #echo($processor_index . " " . $processor_type . "\n");
  if(!$valid_processor[$processor_type][$processor_index]) {
    echo("-");
    mysql_query("DELETE FROM `processors` WHERE processor_id = '" . $test_processor['processor_id'] . "'");
  }
  unset($processor_oid); unset($processor_type);
}

unset($valid_processor);
echo("\n");

?>
