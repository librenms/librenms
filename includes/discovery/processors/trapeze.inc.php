<?php
//
// Discovery of CPU usage on Juniper Wireless (Trapeze) devices.
//
if ($device['os'] == 'trapeze') {
    $descr = 'Processor';
    $usage = snmp_get($device, 'trpzSysCpuLastMinuteLoad.0', '-OQUvs', 'TRAPEZE-NETWORKS-SYSTEM-MIB', 'trapeze');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '1.3.6.1.4.1.14525.4.8.1.1.11.2.0', '0', 'trapeze', $descr, '1', $usage, null, null);
    }
}
unset($descr, $usage);
