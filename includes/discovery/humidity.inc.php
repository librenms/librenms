<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Humidity : ");

$valid_humidity = array();

switch ($device['os'])
{
  case "akcp":
  case "minkelsrms":
    $oids = snmp_walk($device, ".1.3.6.1.4.1.3854.1.2.2.1.16.1.4", "-Osqn", "");
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    if ($oids) echo("AKCP ");
    foreach(explode("\n", $oids) as $data) 
    {
      $data = trim($data);
      if ($data) 
      {
        list($oid,$status) = explode(" ", $data,2);
        if ($status == 2) # 2 = normal, 0 = not connected
        {
          $split_oid = explode('.',$oid);
          $humidity_id = $split_oid[count($split_oid)-1];
          $descr_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.1.$humidity_id";
          $humidity_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.3.$humidity_id";
          $warnlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.7.$humidity_id";
          $limit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.8.$humidity_id";
          $warnlowlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.9.$humidity_id";
          $lowlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.10.$humidity_id";
          
          $descr = trim(snmp_get($device, $descr_oid, "-Oqv", ""),'"');
          $humidity = snmp_get($device, $humidity_oid, "-Oqv", "");
          $warnlimit = snmp_get($device, $warnlimit_oid, "-Oqv", "");
          $limit = snmp_get($device, $limit_oid, "-Oqv", "");
          $lowlimit = snmp_get($device, $lowlimit_oid, "-Oqv", "");
          $warnlowlimit = snmp_get($device, $warnlowlimit_oid, "-Oqv", "");
        
          discover_humidity($valid_humidity, $device, $humidity_oid, $humidity_id, "akcp", $descr, 1, $lowlimit, $warnlowlimit, $limit, $warnlimit, $humidity);
        }
      }
    }
    break;
}

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
