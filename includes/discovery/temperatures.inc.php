<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Temperatures : ");

$valid_temp = array();

include_dir("includes/discovery/temperatures");

if($debug) { print_r($valid_temp); }

$sql = "SELECT * FROM sensors AS S, devices AS D WHERE S.sensor_class='temperature' AND S.device_id = D.device_id AND D.device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_temperature = mysql_fetch_array($query)) 
  {
    $temperature_index = $test_temperature['sensor_index'];
    $temperature_type = $test_temperature['sensor_type'];
    if($debug) { echo($temperature_index . " -> " . $temperature_type . "\n"); }
    if(!$valid_temp[$temperature_type][$temperature_index]) 
    {
      echo("-");
      mysql_query("DELETE FROM `sensors` WHERE sensor_class='temperature' AND sensor_id = '" . $test_temperature['sensor_id'] . "'");
    }
    unset($temperature_oid); unset($temperature_type);
  }
}

unset($valid_temp); echo("\n");

?>
