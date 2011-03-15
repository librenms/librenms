<?php

global $valid_sensor;

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
  foreach (explode("\n", $oids) as $data)
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
      discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, NULL, $warnlimit, $limit, $current);
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

    discover_sensor($valid_sensor, 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, NULL, $warnlimit, $limit, $current);
  }
}

?>