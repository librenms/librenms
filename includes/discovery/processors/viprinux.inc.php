<?php

if ($device['os'] == 'viprinux') {
    $usage = str_replace('"', "", snmp_get($device, 'VIPRINET-MIB::vpnRouterCPULoad.0', '-OvQ'));
    $descr = 'Processor';

    echo 'Viprinet :';

    if (is_numeric($usage)) {
        discover_processor(
            $valid['processor'],
            $device,
            'VIPRINET-MIB::vpnRouterCPULoad.0',
            '0',
            'viprinet-cpu',
            $descr,
            '1',
            $usage,
            null,
            null
        );
    }
}
