<?php

$sensor_names[1] = "other";
$sensor_names[2] = "unknown";
$sensor_names[3] = "system";
$sensor_names[4] = "systemBoard";
$sensor_names[5] = "ioBoard";
$sensor_names[6] = "cpu";
$sensor_names[7] = "memory";
$sensor_names[8] = "storage";
$sensor_names[9] = "removableMedia";
$sensor_names[10] = "powerSupply";
$sensor_names[11] = "ambient";
$sensor_names[12] = "chassis";
$sensor_names[13] = "bridgeCard";


//if ($device['os'] == "ironware")
//{
  echo("HP_ILO ");
  $oids = snmp_walk($device,"1.3.6.1.4.1.232.6.2.6.8.1.2.1","-Osqn","");
  $oids = trim($oids);
  foreach (explode("\n", $oids) as $data)
  {
    $data = trim($data);
    if ($data != "")
    {
      list($oid) = explode(" ", $data);
      $split_oid        = explode('.',$oid);
      $temperature_id   = $split_oid[count($split_oid)-2].".".$split_oid[count($split_oid)-1];

      $descr_oid = "1.3.6.1.4.1.232.6.2.6.8.1.3.$temperature_id";
      $descr = $sensor_names[snmp_get($device,$descr_oid,"-Oqv","")];

      $temperature_oid  = "1.3.6.1.4.1.232.6.2.6.8.1.4.$temperature_id";
      $temperature = snmp_get($device,$temperature_oid,"-Oqv","");

      $threshold_oid = "1.3.6.1.4.1.232.6.2.6.8.1.5.$temperature_id";
      $threshold = snmp_get($device,$threshold_oid,"-Oqv","");

      discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'hpilo', $descr, '2', '1', NULL, NULL, NULL, $threshold, $temperature);
    }
  }
//}

?>
