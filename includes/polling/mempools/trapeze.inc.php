<?php
//
// Polling of Memory usage on Juniper Wireless (Trapeze) devices.
//
if ($device['os'] == 'trapeze') {
    $total = snmp_get($device, 'trpzSysCpuMemorySize.0', '-OQUvs', 'TRAPEZE-NETWORKS-SYSTEM-MIB', 'trapeze');
    $used = snmp_get($device, 'trpzSysCpuMemoryLastMinuteUsage.0', '-OQUvs', 'TRAPEZE-NETWORKS-SYSTEM-MIB', 'trapeze');
	//$used  *= 1024;
	//$total *= 1024;
    $mempool['total'] = ($total * 1024);
    $mempool['used']  = ($used * 1024);
    $mempool['free']  = (($total - $used) * 1024);
	}