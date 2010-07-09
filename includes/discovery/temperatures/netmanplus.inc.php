<?php

global $valid_temp;
  
if($device['os'] == "netmanplus") 
{
  $oids = snmp_walk($device, "1.3.6.1.2.1.33.1.2.7", "-Osqn", "UPS-MIB");
  if ($debug) { echo($oids."\n"); }
  $oids = trim($oids);
  if ($oids) echo("NetMan Plus Battery Temperature ");
  foreach(explode("\n", $oids) as $data)
  {
    $data = trim($data);
    if ($data)
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $temp_id = $split_oid[count($split_oid)-1];
      $temp_oid  = "1.3.6.1.2.1.33.1.2.7.$temp_id";
      $temp = snmp_get($device, $temp_oid, "-Ovq");
      $descr = "Battery" . (count(explode("\n",$oids)) == 1 ? '' : ' ' . ($temp_id+1));
      discover_temperature($valid_temp, $device, $temp_oid, $temp_id, "netmanplus", $descr, 1, NULL, NULL, $temp);
    }
  }
}

?>
