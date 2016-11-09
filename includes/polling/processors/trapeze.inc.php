<?php
//
// Polling of CPU usage on Juniper Wireless (Trapeze) devices.
//
if ($device['os'] == 'trapeze') {
    $proc = snmp_get($device, 'trpzSysCpuLastMinuteLoad.0', '-OQUvs', 'TRAPEZE-NETWORKS-SYSTEM-MIB', 'trapeze');
}
