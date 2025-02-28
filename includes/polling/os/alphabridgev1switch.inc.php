<?php

if ($device['os'] == 'alphabridgev1switch') {
    $cpu_usage = snmp_get($device, '1.3.6.1.4.1.58158.1.439.0', '-Oqv');

    if ($cpu_usage === false) {
        $cpu_usage = snmp_get($device, '1.3.6.1.4.1.58158.1.315.0', '-Oqv');
    }

    if ($cpu_usage !== false) {
        discover_processor($valid['processor'], $device, 1, 'alphabridgev1switch', 'CPU', 1, $cpu_usage);
    }
}
