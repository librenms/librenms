<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Current : ");

## APC PDU
if ($device['os'] == "apc") 
{
  $oids = snmp_walk($device, ".1.3.6.1.4.1.318.1.1.12.2.3.1.1.2", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  $oids = trim($oids);
  if ($oids) echo("APC ");
  $type = "apc";
  $precision = "10";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$kind) = explode(" ", $data);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];

      $current_oid   = "1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.".$index;
      $phase_oid     = "1.3.6.1.4.1.318.1.1.12.2.3.1.1.4.".$index;
      $limit_oid     = "1.3.6.1.4.1.318.1.1.12.2.2.1.1.4.".$index;
      $lowlimit_oid  = "1.3.6.1.4.1.318.1.1.12.2.2.1.1.2.".$index;
      $warnlimit_oid = "1.3.6.1.4.1.318.1.1.12.2.2.1.1.3.".$index;

      $phase     = snmp_get($device, $phase_oid, "-Oqv", "");
      $current   = snmp_get($device, $current_oid, "-Oqv", "") / $precision;
      $limit     = snmp_get($device, $limit_oid, "-Oqv", ""); # No / $precision here! Nice, APC!
      $lowlimit  = snmp_get($device, $lowlimit_oid, "-Oqv", ""); # No / $precision here! Nice, APC!
      $warnlimit = snmp_get($device, $warnlimit_oid, "-Oqv", ""); # No / $precision here! Nice, APC!
      $descr     = "Phase $phase";

      echo discover_current($device, $current_oid, $index, $type, $descr, $precision, $lowlimit, $warnlimit, $limit, $current);
      $current_exists[$type][$index] = 1;
    }
  }
}

## Delete removed sensors

if($debug) { print_r($current_exists); }

$sql = "SELECT * FROM current WHERE device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_current = mysql_fetch_array($query))
  {
    $index = $test_current['current_index'];
    $type = $test_current['current_type'];
    if($debug) { echo("$type -> $index\n"); }
    if(!$current_exists[$type][$index]) {
      echo("-");
      mysql_query("DELETE FROM `current` WHERE current_id = '" . $test_current['current_id'] . "'");
    }
  }
}

unset($current_exists); echo("\n");

?>
