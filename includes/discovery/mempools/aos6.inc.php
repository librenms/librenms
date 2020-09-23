<?php

if ($device['os'] == 'aos6') {
    echo 'Alcatel-Lucent OS: ';
    if (strpos($device['sysObjectID'], '1.3.6.1.4.1.6486.800')) { // AOS 6
        $total = snmp_get($device, 'systemHardwareMemorySize.0', '-OvQ', 'ALCATEL-IND1-SYSTEM-MIB', 'aos6');
        $percent = snmp_get($device, 'healthDeviceMemoryLatest.0', '-OvQ', 'ALCATEL-IND1-HEALTH-MIB', 'aos6');
        $used = ($total / 100 * $perc_used);
        $free = ($total - $used);

        if (is_numeric($total) && is_numeric($used)) {
            discover_mempool($valid_mempool, $device, 0, 'aos6', 'Device Memory', '1', null, null);
        }
    }
}
