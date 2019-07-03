<?php
if ($device['os'] == 'pbn') {
    echo 'PBN-MEMORY-POOL: ';

    // find out wich build number we have
    preg_match('/^.* Build (?<build>\d+)/', $device['version'], $version);
    d_echo($version);

    // specified MIB supported since build 16607
    if ($version['build'] >= 16607) {
        $usage = snmp_get($device, 'nmsMemoryPoolUtilization.0', '-OUvQ', 'NMS-MEMORY-POOL-MIB', 'pbn');

        if (is_numeric($usage)) {
            discover_mempool($valid_mempool, $device, 0, 'pbn-mem', 'Main Memory', '100', null, null);
        }
    }
}
