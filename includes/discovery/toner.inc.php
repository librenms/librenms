<?php

if ($config['enable_printers'])
{

$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Toner : ");

if ($device['os'] == "dell-laser") 
{
  $oids = trim(snmp_walk($device, "SNMPv2-SMI::mib-2.43.12.1.1.2.1 ", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo("Dell ");
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$kind) = explode(" ", $data);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      if ($kind == 1)
      {
        $toner_oid     = ".1.3.6.1.2.1.43.11.1.1.9.1.$index";
        $descr_oid     = ".1.3.6.1.2.1.43.11.1.1.6.1.$index";
        $capacity_oid  = ".1.3.6.1.2.1.43.11.1.1.8.1.$index";
        $descr         = str_replace('"','',snmp_get($device, $descr_oid, "-Oqv"));
        $current       = snmp_get($device, $toner_oid, "-Oqv");
        $capacity      = snmp_get($device, $capacity_oid, "-Oqv");
        $current       = $current / $capacity * 100;
        $type          = "dell-laser";
        echo discover_toner($device, $toner_oid, $index, $type, $descr, $capacity, $current);
        $toner_exists[$type][$index] = 1;
      }
    }
  }
}

## Delete removed toners  

if($debug) { echo("\n Checking ... \n"); print_r($toner_exists); }

$sql = "SELECT * FROM toner WHERE device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_toner = mysql_fetch_array($query))
  {
    $toner_index = $test_toner['toner_index'];
    $toner_type = $test_toner['toner_type'];
    if(!$toner_exists[$toner_type][$toner_index]) {
      echo("-");
      mysql_query("DELETE FROM `toner` WHERE toner_id = '" . $test_toner['toner_id'] . "'");
    }
  }
}

                                      
                                      
unset($toner_exists); echo("\n");

} # if ($config['enable_printers'])
?>
