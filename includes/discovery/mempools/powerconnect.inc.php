<?php

if ($device['os'] == 'powerconnect') {
    echo 'Powerconnect: ';

    $free = snmp_get($device, 'dellLanExtension.6132.1.1.1.1.4.1.0', '-OvQ', 'Dell-Vendor-MIB');

    if (is_numeric($free)) {
        discover_mempool($valid_mempool, $device, 0, 'powerconnect-cpu', 'CPU Memory', '1', null, null);
    }

    echo 'DNOS-MEMORY-POOL:  ';

    $get_series = explode('.', $device['sysObjectID'], 2); // Get series From MIB
    $series = $get_series[0];
    if ($series == 'f10SSeriesProducts') {
        $total = snmp_get($device, 'chSysProcessorMemSize.1', '-OvQU', 'F10-S-SERIES-CHASSIS-MIB');
        $used = $mempool['total'] * (snmp_get($device, 'F10-S-SERIES-CHASSIS-MIB::chStackUnitMemUsageUtil.1', '-OvQU')/ 100);
        $free = ($mempool['total'] - $mempool['used']);
        if (is_numeric($total) && is_numeric($used)) {
            discover_mempool($valid_mempool, $device, 0, 'dnos-mem', 'Memory Utilization', '1', null, null);
        }
    } else {
        $free = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.1.0', '-OvQ');
        if (is_numeric($free)) {
            discover_mempool($valid_mempool, $device, 0, 'dnos-mem', 'Memory Utilization', '1', null, null);
        }
    }
}
