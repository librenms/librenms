<?php

global $valid_temp;
 
if ($device['os'] == "areca") 
{
  $oids = snmp_walk($device, "1.3.6.1.4.1.18928.1.1.2.14.1.2", "-Osqn", "");
  if ($debug) { echo($oids."\n"); }
  $oids = trim($oids);
  if ($oids) echo("Areca Harddisk ");
  foreach(explode("\n", $oids) as $data)
  {
    $data = trim($data);
    if ($data)
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $temp_id = $split_oid[count($split_oid)-1];
      $temp_oid  = "1.3.6.1.4.1.18928.1.1.2.14.1.2.$temp_id";
      $temp  = snmp_get($device, $temp_oid, "-Oqv", "");
      $descr = "Hard disk $temp_id";
      if ($temp != -128) # -128 = not measured/present
      {
        discover_temperature($valid_temp, $device, $temp_oid, zeropad($temp_id), "areca", $descr, 1, NULL, NULL, $temp);
      }
    }
  }

  $oids = snmp_walk($device, "1.3.6.1.4.1.18928.1.2.2.1.10.1.2", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo("Areca Controller ");
  $precision = 1;
  $type = "areca";
  foreach(explode("\n", $oids) as $data)
  {
    $data = trim($data);
    if ($data)
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      $oid  = "1.3.6.1.4.1.18928.1.2.2.1.10.1.3." . $index;
      $current = snmp_get($device, $oid, "-Oqv", "");
      discover_temperature($valid_temp, $device, $oid, $index, $type, trim($descr,'"'), $precision, NULL, NULL, $current);
    }
  }
}

?>
