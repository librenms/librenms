<?php

if ($device['os'] == 'benu') {
    echo 'BENU-CHASSIS-MIB: ';

    $total   = snmp_get($device, 'bSysTotalMem.0', '-OvQs', 'BENU-HOST-MIB');
    $used = snmp_get($device, 'bSysMemUsed.0', '-OvQs', 'BENU-HOST-MIB');
    $free    = snmp_get($device, 'bSysMemFree.0', '-OvQs', 'BENU-HOST-MIB');
    $percent    = ($total / $used) * 100;
}
