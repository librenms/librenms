<?php

echo 'PBN Secure MemPool'.'\n';

if ($device['os'] == 'pbn') {
  $perc     = snmp_get($device, "NMS-MEMORY-POOL-MIB::nmsMemoryPoolUtilization.0", '-OvQ');
  $memory_available = snmp_get($device, "NMS-MEMORY-POOL-MIB::nmsMemoryPoolTotalMemorySize.0", '-OvQ');
  $mempool['total'] = $memory_available;

  if (is_numeric($perc)) {
    $mempool['used'] = ($memory_available / 100 * $perc);
    $mempool['free'] = ($memory_available - $mempool['used']);
  }

  echo "PERC " .$perc."%\n";
  echo "Avail " .$mempool['total']."\n";

}
