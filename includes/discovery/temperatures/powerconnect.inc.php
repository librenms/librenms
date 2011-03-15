<?php

global $valid_sensor;

if ($device['os'] == "powerconnect")
{
  $oids = snmp_get($device, ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.8.1.4.0", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids)
  {
    echo("Powerconnect ");
    list($oid,$current) = explode(' ',$oids);
    $divisor = "1";
    $multiplier = "1";
    $type = "powerconnect";
    $index = "0";
    $descr = "Internal Temperature";

    discover_sensor($valid_sensor, 'temperature', $device, $oid, $index, $type, $descr, $divisor, $multiplier, NULL, NULL, NULL, NULL, $current);
  }
}

?>