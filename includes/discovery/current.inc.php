<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

$valid_current = array();

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

      echo discover_current($valid_current,$device, $current_oid, $index, $type, $descr, $precision, $lowlimit, $warnlimit, $limit, $current);
    }
  }
}

## MGE UPS
if ($device['os'] == "mgeups") 
{
  echo("MGE ");
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.7.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $current_oid   = ".1.3.6.1.4.1.705.1.7.2.1.5.$i";
    $descr      = "Output"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $current_oid, "-Oqv");
    if (!$current)
    {
      $current_oid .= ".0";
      $current    = snmp_get($device, $current_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $precision  = 10;
    $index      = $i;
    $warnlimit  = NULL;
    $lowlimit   = 0;
    $limit      = NULL;
    echo discover_current($valid_current,$device, $current_oid, $index, $type, $descr, $precision, $lowlimit, $warnlimit, $limit, $current);
  }
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.6.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $current_oid   = ".1.3.6.1.4.1.705.1.6.2.1.6.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $current_oid, "-Oqv");
    if (!$current)
    {
      $current_oid .= ".0";
      $current    = snmp_get($device, $current_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $precision  = 10;
    $index      = 100+$i;
    $warnlimit  = NULL;
    $lowlimit   = 0;
    $limit      = NULL;
    echo discover_current($valid_current,$device, $current_oid, $index, $type, $descr, $precision, $lowlimit, $warnlimit, $limit, $current);
  }
}

## Riello UPS
if ($device['os'] == "netmanplus") 
{
  echo("NetMan Plus ");
  
  $oids = snmp_walk($device, "1.3.6.1.2.1.33.1.2.6", "-Osqn", "UPS-MIB");
  if ($debug) { echo($oids."\n"); }
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $current_id = $split_oid[count($split_oid)-1];
      $current_oid  = "1.3.6.1.2.1.33.1.2.6.$current_id";
      $precision = 10;
      $current  = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port $current_oid")) / $precision;
      $descr = "Battery" . (count(explode("\n",$oids)) == 1 ? '' : ' ' . ($current_id+1));
      $type = "netmanplus";
      $index = 500+$current_id;
      discover_current($valid_current,$device, $current_oid, $index, $type, $descr, $precision, NULL, NULL, NULL, $current);
    }
  }

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.4.3.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $current_oid   = ".1.3.6.1.2.1.33.1.4.4.1.3.$i";
    $descr      = "Output"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $current_oid, "-Oqv");
    $type       = "netmanplus";
    $precision  = 1;
    $index      = $i;
    echo discover_current($valid_current,$device, $current_oid, $index, $type, $descr, $precision, NULL, NULL, NULL, $current);
  }

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.3.2.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $current_oid   = "1.3.6.1.2.1.33.1.3.3.1.4.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $current_oid, "-Oqv");
    $type       = "netmanplus";
    $precision  = 1;
    $index      = 100+$i;
    echo discover_current($valid_current,$device, $current_oid, $index, $type, $descr, $precision, NULL, NULL, NULL, $current);
  }

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.5.2.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $current_oid   = "1.3.6.1.2.1.33.1.5.3.1.3.$i";
    $descr      = "Bypass"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $current_oid, "-Oqv");
    $type       = "netmanplus";
    $precision  = 1;
    $index      = 200+$i;
    echo discover_current($valid_current,$device, $current_oid, $index, $type, $descr, $precision, NULL, NULL, NULL, $current);
  }
}


## Delete removed sensors

if($debug) { print_r($valid_current); }

$sql = "SELECT * FROM current WHERE device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_current = mysql_fetch_array($query))
  {
    $index = $test_current['current_index'];
    $type = $test_current['current_type'];
    if($debug) { echo("$type -> $index\n"); }
    if(!$valid_current[$type][$index]) {
      echo("-");
      mysql_query("DELETE FROM `current` WHERE current_id = '" . $test_current['current_id'] . "'");
    }
  }
}

unset($valid_current); echo("\n");

?>
