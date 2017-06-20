<?php

if ($device['os'] == 'benuos') {
    echo 'BENU-HOST-MIB: ';

    $total   = snmp_get($device, 'bSysTotalMem.0', '-OvQs', 'BENU-HOST-MIB');
    $used    = snmp_get($device, 'bSysMemUsed.0', '-OvQs', 'BENU-HOST-MIB');
    $free    = snmp_get($device, 'bSysMemFree.0', '-OvQs', 'BENU-HOST-MIB');
    $percent    = ($total / $used) * 100;
    if (is_numeric($total) && is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, 'benuos', 'Memory', '1', null, null);
    }
}
