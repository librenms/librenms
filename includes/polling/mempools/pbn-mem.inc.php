<?php

echo 'PBN MemPool'.'\n';

if ($device['os'] == 'pbn') {
    // find out wich build number we have
    preg_match('/^.* Build (?<build>\d+)/', $device['version'], $version);
    d_echo($version);

    // specified MIB supported since build 16607
    if ($version[build] >= 16607) {
        $mibdir = $config['mibdir'].'/pbn'.':'.$config['mibdir'];
        $perc = snmp_get($device, 'nmsMemoryPoolUtilization.0', '-OUvQ', 'NMS-MEMORY-POOL-MIB', $mibdir);
        $memory_available = snmp_get($device, 'nmsMemoryPoolTotalMemorySize.0', '-OUvQ', 'NMS-MEMORY-POOL-MIB', $mibdir);
        $mempool['total'] = $memory_available;

        if (is_numeric($perc)) {
            $mempool['used'] = ($memory_available / 100 * $perc);
            $mempool['free'] = ($memory_available - $mempool['used']);
        }

        echo "PERC " .$perc."%\n";
        echo "Avail " .$mempool['total']."\n";
    }
}
