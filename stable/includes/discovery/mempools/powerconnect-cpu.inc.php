<?php

if ($device['os'] == "powerconnect")
{
  echo("Powerconnect: ");

  $free = snmp_get($device, "dellLanExtension.6132.1.1.1.1.4.1.0", "-OvQ", "Dell-Vendor-MIB");

  if (is_numeric($free))
  {
    discover_mempool($valid_mempool, $device, 0, "powerconnect-cpu", "CPU Memory", "1", NULL, NULL);
  }
}
?>
