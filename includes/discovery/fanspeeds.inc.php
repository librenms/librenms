<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

$valid_fan = array();

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
      $oid  = "1.3.6.1.4.1.2021.13.16.3.1.3.". $index;
      $current = snmp_get($device, $oid, "-Oqv", "LM-SENSORS-MIB");
      $descr = trim(str_ireplace("fan-", "", $descr));
      if($current > '0' && $current < '500') {
        discover_fan($valid_fan,$device, $oid, $index, $type, $descr, $precision, NULL, NULL, $current);
      }
    }
  }
}

## Areca Fanspeeds
if ($device['os'] == "areca") 
{
  $oids = snmp_walk($device, "1.3.6.1.4.1.18928.1.2.2.1.9.1.2", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo("Areca ");
  $precision = 1;
  $type = "areca";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      $oid  = "1.3.6.1.4.1.18928.1.2.2.1.9.1.3." . $index;
      $current = snmp_get($device, $oid, "-Oqv", "") / $precision;
      discover_fan($valid_fan,$device, $oid, $index, $type, trim($descr,'"'), $precision, NULL, NULL, $current);
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
#        $precision     = snmp_get($device, $precision_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
# This returns an incorrect precision. At least using the raw value... I think. -TL
        $precision     = 1;
        $monitor       = snmp_get($device, $monitor_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
        $descr         = str_replace(' Fan Speed','',$descr);
        $descr         = str_replace(' Speed','',$descr);
                
        if ($monitor == 'true')
        {
          echo discover_fan($valid_fan,$device, $fan_oid, $index, $type, $descr, $precision, $limit, NULL, $current);
        }
      }
    }
  }
}

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
