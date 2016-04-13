<?php
// Simple hard-coded poller for Pulse Secure
echo 'Viprinet Secure CPU Usage';

if ($device['os'] == 'viprinet') {
    $usage = str_replace('"',"", snmp_get($device, 'VIPRINET-MIB::vpnRouterCPULoad.0', '-OvQ'));
    if (is_numeric($usage)) {
        $proc = ($usage * 100);
    }
}
