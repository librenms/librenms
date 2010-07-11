<?php

global $valid_temp;
 
if ($device['os'] == "apc") 
{
  $current = snmp_get($device, "1.3.6.1.4.1.318.1.1.1.2.2.2.0", "-OsqnU", "");
  if ($debug) { echo($current."\n"); }
  if ($current)
  {
    echo("APC UPS Internal ");
    $precision = 1;
    $type = "apc";
    $index = 0;
    $oid  = "1.3.6.1.4.1.318.1.1.1.2.2.2.0";
    $descr = "Internal Temperature";
    discover_temperature($valid_temp, $device, $oid, $index, $type, trim($descr,'"'), $precision, NULL, NULL, $current);
  }
}

?>
