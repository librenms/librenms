<?php

$valid_fan = array();

echo("Fanspeeds : ");

include_dir("includes/discovery/fanspeeds");

## Delete removed sensors

if($debug) { echo("\n Checking ... \n"); print_r($valid_fan); }

$sql = "SELECT * FROM sensors WHERE sensor_class='fanspeed' AND device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_fan = mysql_fetch_array($query)) 
  {  
    $fan_index = $test_fan['sensor_index'];
    $fan_type = $test_fan['sensor_type'];
    if($debug) { echo("$fan_type -> $fan_index\n"); }
    if(!$valid_fan[$fan_type][$fan_index]) {
      echo("-");
      mysql_query("DELETE FROM `fanspeed` WHERE sensor_id = '" . $test_fan['sensor_id'] . "'");
    }
  }
}

unset($valid_fan); echo("\n");

?>
