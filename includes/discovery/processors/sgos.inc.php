<?php
//
// Hardcoded discovery of CPU usage on ProxySG devices.
//
if ($device['os'] == 'sgos') {
    echo 'ProxySG CPU : ';

    $descr = 'Processor';
    $procs = snmp_walk($device, 'BLUECOAT-SG-PROXY-MIB::sgProxyCpuCoreBusyPerCent', '-Osqn');

    foreach (explode("\n", $procs) as $i => $t) {
        $t = explode(' ', $t);
        $oid = $t[0];
        $val = $t[1];
        discover_processor($valid['processor'], $device, $oid, zeropad($i + 1), 'proxysg-cpu', 'Processor '.($i + 1), '1', $val, $i, null);
    }
}
