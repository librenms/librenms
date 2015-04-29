<?php

if ($device['os'] == "ironware" || $device['os_type'] == "ironware")
{
  echo("Ironware Dynamic: ");

  $percent = snmp_get($device, "snAgGblDynMemUtil.0", "-OvQ", "FOUNDRY-SN-AGENT-MIB");

  if (is_numeric($percent))
  {
    discover_mempool($valid_mempool, $device, 0, "ironware-dyn", "Dynamic Memory", "1", NULL, NULL);
  }
}
?>
