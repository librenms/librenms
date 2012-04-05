<?php

if ($device['os'] == "powerconnect")
{
  $temps = snmp_walk($device, "boxServicesTempSensorTemperature", "-OsqnU", "FASTPATH-BOXSERVICES-PRIVATE-MIB");
  if ($debug) { echo($temps."\n"); }

  $index = 0;
  foreach (explode("\n",$temps) as $oids)
  {
    echo("Powerconnect ");
    list($oid,$current) = explode(' ',$oids);
    $divisor = "1";
    $multiplier = "1";
    $type = "powerconnect";
    $index++;
    $descr = "Internal Temperature";
    if (count(explode("\n",$temps)) > 1) { $descr .= " $index"; }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, $multiplier, NULL, NULL, NULL, NULL, $current);
  }
}

?>
