<?php

if (strstr($device['hardware'], "dell"))
{
  $oids = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -m MIB-Dell-10892 -$snmpver -CI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.674.10892.1.700.20.1.8");
  $oids = trim($oids);
  if ($oids) echo("Dell OMSA ");
  foreach(explode("\n",$oids) as $oid)
  {
    $oid = substr(trim($oid), 36);
    list($oid) = explode(" ", $oid);
    if ($oid != "")
    {
      $descr_query = $config['snmpget'] . " -M " . $config['mibdir'] . " -m MIB-Dell-10892 -$snmpver  -Onvq -c $community $hostname:$port .1.3.6.1.4.1.674.10892.1.700.20.1.8.$oid";
      $descr = trim(str_replace("\"", "", shell_exec($descr_query)));
      $fulloid = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.$oid";
      discover_temperature($valid_temp, $device, $fulloid, $oid, "dell", $descr, "10", NULL, NULL, NULL);
    }
  }
}


?>
