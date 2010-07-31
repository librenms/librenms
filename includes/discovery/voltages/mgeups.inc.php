<?php

global $valid_sensor;

## MGE UPS Voltages
if ($device['os'] == "mgeups") 
{
  echo("MGE ");
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.7.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $volt_oid   = ".1.3.6.1.4.1.705.1.7.2.1.2.$i";
    $descr      = "Output"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    if (!$current) 
    {
      $volt_oid .= ".0";
      $current    = snmp_get($device, $volt_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $divisor  = 10;
    $index      = $i;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.6.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $volt_oid   = ".1.3.6.1.4.1.705.1.6.2.1.2.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    if (!$current) 
    {
      $volt_oid .= ".0";
      $current    = snmp_get($device, $volt_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $divisor  = 10;
    $index      = 100+$i;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }
}
?>