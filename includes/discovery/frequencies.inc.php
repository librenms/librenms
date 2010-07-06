<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

$valid_freq = array();

echo("Frequencies : ");

## MGE UPS Frequencies
if ($device['os'] == "mgeups") 
{
  echo("MGE ");
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.7.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $freq_oid   = ".1.3.6.1.4.1.705.1.7.2.1.3.$i";
    $descr      = "Output"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $freq_oid, "-Oqv");
    if (!$current)
    {
      $freq_oid .= ".0";
      $current    = snmp_get($device, $freq_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $precision  = 10;
    $index      = $i;
    echo discover_freq($valid_freq, $device, $freq_oid, $index, $type, $descr, $precision, $lowlimit, $limit, $current);
  }
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.6.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $freq_oid   = ".1.3.6.1.4.1.705.1.6.2.1.3.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $freq_oid, "-Oqv");
    if (!$current)
    {
      $freq_oid .= ".0";
      $current    = snmp_get($device, $freq_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $precision  = 10;
    $index      = 100+$i;
    echo discover_freq($valid_freq, $device, $freq_oid, $index, $type, $descr, $precision, $lowlimit, $limit, $current);
  }
}

## Riello UPS
if ($device['os'] == "netmanplus") 
{
  echo("NetMan Plus ");
  
  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.3.2.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $freq_oid   = "1.3.6.1.2.1.33.1.3.3.1.2.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $freq_oid, "-Oqv") / 10;
    $type       = "netmanplus";
    $precision  = 10;
    $index      = '3.2.0.'.$i;
    echo discover_freq($valid_freq, $device, $freq_oid, $index, $type, $descr, $precision, NULL, NULL, $current);
  }

  $freq_oid   = "1.3.6.1.2.1.33.1.4.2.0";
  $descr      = "Output";
  $current    = snmp_get($device, $freq_oid, "-Oqv") / 10;
  $type       = "netmanplus";
  $precision  = 10;
  $index      = '4.2.0';
  echo discover_freq($valid_freq, $device, $freq_oid, $index, $type, $descr, $precision, NULL, NULL, $current);

  $freq_oid   = "1.3.6.1.2.1.33.1.5.1.0";
  $descr      = "Bypass";
  $current    = snmp_get($device, $freq_oid, "-Oqv") / 10;
  $type       = "netmanplus";
  $precision  = 10;
  $index      = '5.1.0';
  echo discover_freq($valid_freq, $device, $freq_oid, $index, $type, $descr, $precision, NULL, NULL, $current);
}

## APC
if ($device['os'] == "apc") 
{
  $oids = snmp_walk($device, "1.3.6.1.4.1.318.1.1.8.5.3.2.1.4", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo("APC In ");
  $precision = 1;
  $type = "apc";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$current) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      $oid  = "1.3.6.1.4.1.318.1.1.8.5.3.2.1.4." . $index;
      $descr = "Input Feed " . chr(64+$index);
      discover_freq($valid_freq,$device, $oid, "3.2.1.4.$index", $type, $descr, $precision, NULL, NULL, $current);
    }
  }

  $oids = snmp_walk($device, "1.3.6.1.4.1.318.1.1.8.5.4.2.1.4", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo(" APC Out ");
  $precision = 1;
  $type = "apc";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$current) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-3];
      $oid  = "1.3.6.1.4.1.318.1.1.8.5.4.2.1.4." . $index;
      $descr = "Output Feed"; if (count(explode("\n", $oids)) > 1) { $descr .= " $index"; }
      discover_freq($valid_freq,$device, $oid, "4.2.1.4.$index", $type, $descr, $precision, NULL, NULL, $current);
    }
  }
}



## Delete removed sensors

if($debug) { print_r($valid_freq); }

$sql = "SELECT * FROM frequency WHERE device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_freq = mysql_fetch_array($query))
  {
    $index = $test_freq['freq_index'];
    $type = $test_freq['freq_type'];
    if($debug) { echo("$type -> $index\n"); }
    if(!$valid_freq[$type][$index]) {
      echo("-");
      mysql_query("DELETE FROM `frequency` WHERE freq_id = '" . $test_freq['freq_id'] . "'");
    }
  }
}

unset($fan_exists); echo("\n");

?>
