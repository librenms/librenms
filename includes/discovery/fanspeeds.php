<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Fanspeeds : ");

## LMSensors Fanspeeds
if ($device['os'] == "linux") 
{
  $oids = snmp_walk($device, "lmFanSensorsDevice", "-OsqnU", "LM-SENSORS-MIB");
  if ($debug) { echo($oids."\n"); }
  $oids = trim($oids);
  if ($oids) echo("LM-SENSORS ");
  $precision = 1;
  $type = 'lmsensors';
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      $oid  = ".1.3.6.1.4.1.2021.13.16.3.1.3.". $index;
      $current = snmp_get($device, $oid, "-Oqv", "LM-SENSORS-MIB");
      $descr = trim(str_ireplace("fan-", "", $descr));
      discover_fan($device, $oid, $index, $type, $descr, $precision, NULL, NULL, $current);
      $fan_exists[$type][$index] = 1;
    }
  }
}


## Supermicro Fanspeeds
if ($device['os'] == "linux") 
{
  $oids = snmp_walk($device, "1.3.6.1.4.1.10876.2.1.1.1.1.3", "-OsqnU", "SUPERMICRO-HEALTH-MIB");
  if ($debug) { echo($oids."\n"); }
  $oids = trim($oids);
  if ($oids) echo("Supermicro ");
  $type = "supermicro";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$kind) = explode(" ", $data);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      if ($kind == 0)
      {
        $fan_oid       = "1.3.6.1.4.1.10876.2.1.1.1.1.4.$index";
        $descr_oid     = "1.3.6.1.4.1.10876.2.1.1.1.1.2.$index";
        $limit_oid     = "1.3.6.1.4.1.10876.2.1.1.1.1.6.$index";
        $precision_oid = "1.3.6.1.4.1.10876.2.1.1.1.1.9.$index";
        $monitor_oid   = "1.3.6.1.4.1.10876.2.1.1.1.1.10.$index";
        $descr         = snmp_get($device, $descr_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
        $current       = snmp_get($device, $fan_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
        $limit         = snmp_get($device, $limit_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
        $precision     = snmp_get($device, $precision_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
        $monitor       = snmp_get($device, $monitor_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
        if ($monitor == 'true')
        {
          echo discover_fan($device, $fan_oid, $index, $type, $descr, $precision, $limit, NULL, $current);
          $fan_exists[$type][$index] = 1;
        }
      }
    }
  }
}

## Delete removed sensors

if($debug) { echo("\n Checking ... \n"); print_r($fan_exists); }

$sql = "SELECT * FROM fanspeed WHERE device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_fan = mysql_fetch_array($query)) 
  {  
    $fan_index = $test_fan['fan_index'];
    $fan_type = $test_fan['fan_type'];
    if($debug) { echo("$fan_type -> $fan_index\n"); }
    if(!$fan_exists[$fan_type][$fan_index]) {
      echo("-");
      mysql_query("DELETE FROM `fanspeed` WHERE fan_id = '" . $test_fan['fan_id'] . "'");
    }
  }
}

unset($fan_exists); echo("\n");

?>
