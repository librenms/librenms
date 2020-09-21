<?php

if ($device['os'] == 'aos7') {
    echo 'Alcatel-Lucent OS: ';
    if (strpos($device['sysObjectID'], '1.3.6.1.4.1.6486.801')) { // AOS 7
        $total = snmp_get($device, '.1.3.6.1.4.1.6486.801.1.1.1.2.1.1.3.4.0', '-OvQ', 'ALCATEL-IND1-SYSTEM-MIB', 'nokia/aos7'); // systemHardwareMemorySize
        $percent = snmp_get($device, '.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.8.0', '-OvQ', 'ALCATEL-IND1-HEALTH-MIB', 'nokia/aos7'); // healthModuleMemory1MinAvg
        $used = $total / 100 * $percent;
        $free = ($total - $used);

        if (is_numeric($total) && is_numeric($percent)) {
            $total *= 1024; // Memory in MB
            discover_mempool($valid_mempool, $device, 0, 'aos7', 'Device Memory', 1, null, null);
        }
    }
}
