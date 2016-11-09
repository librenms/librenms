<?php
//
// Discovery of Memory usage on Juniper Wireless (Trapeze) devices.
//
if ($device['os'] == 'trapeze') {
    $descr  = 'Memory';
    $used = snmp_get($device, 'trpzSysCpuMemoryLastMinuteUsage', '-OQUvs', 'TRAPEZE-NETWORKS-SYSTEM-MIB', 'trapeze');
    $total = snmp_get($device, 'trpzSysCpuMemorySize', '-OQUvs', 'TRAPEZE-NETWORKS-SYSTEM-MIB', 'trapeze');
    if (is_numeric($used) && is_numeric($total)) {
        discover_mempool($valid_mempool, $device, 0, 'trapeze', $descr, '1', null, null);
    }
}
unset($descr, $total, $used);
