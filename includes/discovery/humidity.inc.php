<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Humidity : ");

$valid_humidity = array();

include_dir("includes/discovery/humidity");

if($debug) { print_r($valid_humidity); }

$sql = "SELECT * FROM sensors AS S, devices AS D WHERE S.sensor_class='humidity' AND S.device_id = D.device_id AND D.device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_humidity = mysql_fetch_array($query)) 
  {
    $humidity_index = $test_humidity['sensor_index'];
    $humidity_type = $test_humidity['sensor_type'];
    if($debug) { echo($humidity_index . " -> " . $humidity_type . "\n"); }
    if(!$valid_humidity[$humidity_type][$humidity_index]) 
    {
      echo("-");
      mysql_query("DELETE FROM `sensors` WHERE sensor_class='humidity' AND sensor_id = '" . $test_humidity['sensor_id'] . "'");
    }
    unset($humidity_oid); unset($humidity_type);
  }
}

unset($valid_humidity); echo("\n");

?>
