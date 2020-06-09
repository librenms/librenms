<?php

if ($device['os'] === 'benuos') {
    echo 'BENU-HOST-MIB: ';
    $memdata = snmp_get_multi($device, ['bSysTotalMem.0', 'bSysMemUsed.0', 'bSysMemFree.0'], '-OQUs', 'BENU-HOST-MIB');
    $total   = $memdata[0]['bSysTotalMem'];
    $used    = $memdata[0]['bSysMemUsed'];
    $free    = $memdata[0]['bSysMemFree'];
    $percent    = ($total / $used) * 100;
    if (is_numeric($total) && is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, 'benuos', 'Memory', '1', null, null);
    }
}
