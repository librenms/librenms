<?php

global $valid_temp;
 
if ($device['os'] == "apc") 
{
  $oids = snmp_get($device, "1.3.6.1.4.1.318.1.1.1.2.2.2.0", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids)
  {
    echo("APC UPS Internal ");
    list($oid,$current) = explode(' ',$oids);
    $precision = 1;
    $type = "apc";
    $index = 0;
    $descr = "Internal Temperature";
    discover_temperature($valid_temp, $device, $oid, $index, $type, $descr, $precision, NULL, NULL, $current);
  }
}

?>
