<?php
echo("Current : ");

## APC
if ($device['os'] == "apc")
{
  # PDU
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
      if (count(explode("\n",$oids)) != 1)
      {
        $descr     = "Phase $phase";
      }
      else
      {
        $descr     = "Output";
      }
      echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, NULL, $warnlimit, $limit, $current);
    }
  }

  # ATS  
  $atsCurrent = snmp_get($device, "1.3.6.1.4.1.318.1.1.8.5.4.3.1.4.1.1.1", "-OsqnU", "");
  if ($atsCurrent)
  {
      $current_oid   = "1.3.6.1.4.1.318.1.1.8.5.4.3.1.4.1.1.1";
      $limit_oid     = "1.3.6.1.4.1.318.1.1.8.4.16.1.5.1";
      $lowlimit_oid  = "1.3.6.1.4.1.318.1.1.8.4.16.1.3.1";
      $warnlimit_oid = "1.3.6.1.4.1.318.1.1.8.4.16.1.4.1";
      $index         = 1;

      $current   = snmp_get($device, $current_oid, "-Oqv", "") / $precision;
      $limit     = snmp_get($device, $limit_oid, "-Oqv", ""); # No / $precision here! Nice, APC!
      $lowlimit  = snmp_get($device, $lowlimit_oid, "-Oqv", ""); # No / $precision here! Nice, APC!
      $warnlimit = snmp_get($device, $warnlimit_oid, "-Oqv", ""); # No / $precision here! Nice, APC!
      $descr     = "Output Feed";

      echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, NULL, $warnlimit, $limit, $current);

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
    unset($current);
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
    $lowwarnlimit = NULL;
    echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $current);

  }
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.6.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    unset($current);
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
    $lowwarnlimit = NULL;
    echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $current);
  }
}

## Riello UPS
if ($device['os'] == "netmanplus" || $device['os'] == 'deltaups') 
{
  echo("RFC1628 ");
  
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
      $current = snmp_get($device, $current_oid, "-O vq") / $precision;
      $descr = "Battery" . (count(explode("\n",$oids)) == 1 ? '' : ' ' . ($current_id+1));
      $type = "rfc1628";
      $index = 500+$current_id;
      echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '10', '1', NULL, NULL, NULL, NULL, $current);
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
    $type       = "rfc1628";
    $precision  = 1;
    $index      = $i;
    echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '1', '1', NULL, NULL, NULL, NULL, $current);
  }

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.3.2.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $current_oid   = "1.3.6.1.2.1.33.1.3.3.1.4.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $current_oid, "-Oqv");
    $type       = "rfc1628";
    $precision  = 1;
    $index      = 100+$i;
    echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '1', '1', NULL, NULL, NULL, NULL, $current);
  }

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.5.2.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $current_oid   = "1.3.6.1.2.1.33.1.5.3.1.3.$i";
    $descr      = "Bypass"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $current_oid, "-Oqv");
    $type       = "rfc1628";
    $precision  = 1;
    $index      = 200+$i;
    echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '1', '1', NULL, NULL, NULL, NULL, $current);
  }
}



if ($device['os'] == "gamatronicups") {

               for($i = 1; $i <= 3 ;$i++) {
                       $current_oid   = "GAMATRONIC-MIB::gamatronicLTD.5.4.1.1.3.$i";
                       $descr = "Input Phase $i";
                       $current = snmp_get($device, $current_oid, "-Oqv");
                       $type = "gamatronicups";
                       $precision = 1;
                       $index = $i;
                       $lowlimit = 0;
                       $warnlimit = NULL;
                       $limit = NULL;

                       echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '1', '1', $lowlimit, NULL, NULL, NULL, $current);
               }




               for($i = 1; $i <= 3 ;$i++) {
                       $current_oid   = "GAMATRONIC-MIB::gamatronicLTD.5.5.1.1.3.$i";
                       $descr = "Output Phase $i";
                       $current = snmp_get($device, $current_oid, "-Oqv");
                       $type = "gamatronicups";
                       $precision = 1;
                       $index = 100+$i;
                       $lowlimit = 0;
                       $warnlimit = NULL;
                       $limit = NULL;
                       echo discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '1', '1', $lowlimit, NULL, NULL, NULL, $current);
               }

}

if($debug) { print_r($valid['current']); }

check_valid_sensors($device, 'current', $valid_sensor);

echo("\n");

?>
