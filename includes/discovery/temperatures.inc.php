<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Temperatures : ");

$valid_temp = array();

#include("temperatures/adva.inc.php"); ## Disabled needing rewrite
include("temperatures/akcp.inc.php");
include("temperatures/areca.inc.php");
include("temperatures/cisco-envmon.inc.php");
include("temperatures/dell.inc.php");
include("temperatures/ironware.inc.php");
include("temperatures/junose.inc.php");
include("temperatures/junos.inc.php");
include("temperatures/lm-sensors.inc.php");
include("temperatures/netmanplus.inc.php");
include("temperatures/observer-custom.inc.php");
include("temperatures/papouch-tme.inc.php");
include("temperatures/supermicro.inc.php");

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
