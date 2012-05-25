<?php

///
//  Hardcoded discovery of cpu usage on Fortigate devices.
///
//  FORTINET-FORTIGATE-MIB::fgSysCpuUsage.0

if ($device['os'] == "fortigate")
{
  echo("Fortigate : ");

  $descr = "Processor";
  $usage = snmp_get($device, ".1.3.6.1.4.1.12356.101.4.1.3.0", "-Ovq");

  if (is_numeric($usage))
  {
    discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.12356.101.4.1.3.0", "0", "fortigate-fixed", $descr, "1", $usage, NULL, NULL);
  }
}

unset ($processors_array);

?>
