<?php

global $valid_sensor;

## RFC1628
if ($device['os'] == "netmanplus" || $device['os'] == "deltaups") 
{
  echo("RFC1628 ");
  
  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.3.2.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $freq_oid   = "1.3.6.1.2.1.33.1.3.3.1.2.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $freq_oid, "-Oqv") / 10;
    $type       = "rfc1628";
    $divisor  = 10;
    $index      = '3.2.0.'.$i;
    echo discover_sensor($valid_sensor, 'freq', $device, $freq_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }

  $freq_oid   = "1.3.6.1.2.1.33.1.4.2.0";
  $descr      = "Output";
  $current    = snmp_get($device, $freq_oid, "-Oqv") / 10;
  $type       = "rfc1628";
  $divisor  = 10;
  $index      = '4.2.0';
  echo discover_sensor($valid_sensor, 'freq', $device, $freq_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);

  $freq_oid   = "1.3.6.1.2.1.33.1.5.1.0";
  $descr      = "Bypass";
  $current    = snmp_get($device, $freq_oid, "-Oqv") / 10;
  $type       = "rfc1628";
  $divisor  = 10;
  $index      = '5.1.0';
  echo discover_sensor($valid_sensor, 'freq', $device, $freq_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
}
?>
