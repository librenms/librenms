<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

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
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    if (!$current)
    {
      $volt_oid .= ".0";
      $current    = snmp_get($device, $volt_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $precision  = 10;
    $index      = $i;
    if ($current > 55 && $current < 65) 
    {
      #FIXME Are these sensible values?
      $lowlimit = 58;
      $limit = 62;
    }
    else if ($current > 45 && $current < 55) 
    {
      #FIXME Are these sensible values?
      $lowlimit = 48;
      $limit = 52;
    }
    else
    {
      $lowlimit = 0;
      $limit = 0;
    }
    echo discover_freq($device, $freq_oid, $index, $type, $descr, $precision, $lowlimit, $limit, $current);
    $freq_exists[$type][$index] = 1;
  }
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.6.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $freq_oid   = ".1.3.6.1.4.1.705.1.6.2.1.3.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    if (!$current)
    {
      $volt_oid .= ".0";
      $current    = snmp_get($device, $volt_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $precision  = 10;
    $index      = 100+$i;
    if ($current > 55 && $current < 65) 
    {
      #FIXME Are these sensible values?
      $lowlimit = 58;
      $limit = 62;
    }
    else if ($current > 45 && $current < 55) 
    {
      #FIXME Are these sensible values?
      $lowlimit = 48;
      $limit = 52;
    }
    else
    {
      $lowlimit = 0;
      $limit = 0;
    }
    echo discover_freq($device, $freq_oid, $index, $type, $descr, $precision, $lowlimit, $limit, $current);
    $freq_exists[$type][$index] = 1;
  }
}


## Delete removed sensors

if($debug) { print_r($freq_exists); }

$sql = "SELECT * FROM frequency WHERE device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_freq = mysql_fetch_array($query))
  {
    $index = $test_freq['freq_index'];
    $type = $test_freq['freq_type'];
    if($debug) { echo("$type -> $index\n"); }
    if(!$freq_exists[$type][$index]) {
      echo("-");
      mysql_query("DELETE FROM `frequency` WHERE freq_id = '" . $test_freq['freq_id'] . "'");
    }
  }
}

unset($fan_exists); echo("\n");

?>
