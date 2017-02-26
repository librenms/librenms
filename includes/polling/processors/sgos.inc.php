<?php

echo 'ProxySG CPU Usage';

if ($device['os'] == 'sgos') {
    $usage = str_replace('"', "", snmp_get($device, 'BLUECOAT-SG-PROXY-MIB::sgProxyCpuCoreBusyPerCent.0', '-OvQ'));

    if (is_numeric($usage)) {
        $proc = ($usage * 100);
    }
}
