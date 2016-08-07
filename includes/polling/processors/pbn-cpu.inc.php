<?php

if ($device['os'] == 'pbn') {
    
    echo 'PBN CPU Usage';

    // find out wich build number we have
    preg_match('/^.* Build (?<build>\d+)/', $device['version'], $version);
    d_echo($version);

    // specified MIB supported since build 16607
    if ($version[build] >= 16607) {

        $mibdir = $config['mibdir'].'/pbn'.':'.$config['mibdir'];
        $usage = snmp_get($device, 'nmspmCPUTotal5min.1', '-OUvQ', 'NMS-PROCESS-MIB', $mibdir);

        if (is_numeric($usage)) {
            $proc = ($usage * 100);
        }
    }
}
