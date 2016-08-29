<?php
/**
 * Created by PhpStorm.
 * User: crc
 * Date: 8/29/16
 * Time: 2:41 AM
 */

if ($device['os'] == "edgeswitch") {
    d_echo('EdgeSwitch Memory:');
    //EdgeSwitch-SWITCHING-MIB::agentSwitchCpuProcessMemFree
    $avail = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.1.0', '-Oqv');
    //EdgeSwitch-SWITCHING-MIB::agentSwitchCpuProcessMemAvailable
    $total = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.2.0', '-Oqv');
    $used = $total - $avail;
    $percent = ($used / $total * 100);

    if ((is_numeric($total)) && (is_numeric($avail))) {
        discover_mempool($valid_mempool, $device, 0, 'edgeswitch', 'Memory', '1', null, null);
    }
}