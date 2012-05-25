<?php

//  Hardcoded discovery of device CPU usage on Alcatel-Lucent Omniswitches.

if ($device['os'] == "aos")
{
  echo("Alcatel-Lucent Device : ");

  $descr = "Device CPU";
  $usage = snmp_get($device, "1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.13.0", "-OQUvs", "ALCATEL-IND1-HEALTH-MIB", $config['mib_dir'].":".$config['mib_dir']."/aos");

  if (is_numeric($usage))
  {
    discover_processor($valid['processor'], $device, "1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.13.0", "0", "aos-system", $descr, "1", $usage, NULL, NULL);
  }
}

unset ($processors_array);

?>
