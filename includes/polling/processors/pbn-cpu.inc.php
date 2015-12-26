<?php
echo 'PBN CPU Usage';

if ($device['os'] == 'pbn') {
    $usage = str_replace(' percent', '', snmp_get($device, 'NMS-PROCESS-MIB::nmspmCPUTotal5min', '-OvQ'));

    if (is_numeric($usage)) {
        $proc = ($usage * 100);
    }
}
