<?php

echo 'PBN MemPool'.'\n';

if ($device['os'] == 'pbn') {
    
    // find out wich build number we have
    preg_match('/^.* Build (?<build>\d+)/', $device['version'], $version);
    d_echo($version);

    // specified MIB supported since build 16607
    if ($version[build] >= 16607) {
        $perc     = snmp_get($device, "NMS-MEMORY-POOL-MIB::nmsMemoryPoolUtilization.0", '-OUvQ');
        $memory_available = snmp_get($device, "NMS-MEMORY-POOL-MIB::nmsMemoryPoolTotalMemorySize.0", '-OUvQ');
        $mempool['total'] = $memory_available;

        if (is_numeric($perc)) {
            $mempool['used'] = ($memory_available / 100 * $perc);
            $mempool['free'] = ($memory_available - $mempool['used']);
        }

  echo "PERC " .$perc."%\n";
  echo "Avail " .$mempool['total']."\n";
    }
}
