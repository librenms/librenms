<?php

// JUNOS Processors
if ($device['os'] == 'junos') {
    echo 'JUNOS : ';
    $processors_array = snmpwalk_cache_multi_oid($device, 'jnxOperatingCPU', $processors_array, 'JUNIPER-MIB', 'junos');
    $processors_array = snmpwalk_cache_multi_oid($device, 'jnxOperatingDRAMSize', $processors_array, 'JUNIPER-MIB', 'junos');
    $processors_array = snmpwalk_cache_multi_oid($device, 'jnxOperatingMemory', $processors_array, 'JUNIPER-MIB', 'junos');
    $processors_array = snmpwalk_cache_multi_oid($device, 'jnxOperatingDescr', $processors_array, 'JUNIPER-MIB', 'junos');
    d_echo($processors_array);

    if (is_array($processors_array)) {
        foreach ($processors_array as $index => $entry) {
            if (strlen(strstr($entry['jnxOperatingDescr'], 'Routing Engine')) || $entry['jnxOperatingDRAMSize'] && !strpos($entry['jnxOperatingDescr'], 'sensor') && !strstr($entry['jnxOperatingDescr'], 'fan')) {
                if (stripos($entry['jnxOperatingDescr'], 'sensor') || stripos($entry['jnxOperatingDescr'], 'fan')) {
                    continue;
                }

                d_echo($index.' '.$entry['jnxOperatingDescr'].' -> '.$entry['jnxOperatingCPU'].' -> '.$entry['jnxOperatingDRAMSize']."\n");

                $usage_oid = '.1.3.6.1.4.1.2636.3.1.13.1.8.'.$index;
                $descr     = $entry['jnxOperatingDescr'];
                $usage     = $entry['jnxOperatingCPU'];
                if (!strstr($descr, 'No') && !strstr($usage, 'No') && $descr != '') {
                    discover_processor($valid['processor'], $device, $usage_oid, $index, 'junos', $descr, '1', $usage, null, null);
                }
            }
        }
    }

    $srx_processors_array = snmpwalk_cache_multi_oid($device, 'jnxJsSPUMonitoringCPUUsage', $srx_processors_array, 'JUNIPER-SRX5000-SPU-MONITORING-MIB', 'junos');
    d_echo($processors_array);

    if (is_array($srx_processors_array)) {
        foreach ($srx_processors_array as $index => $entry) {
            if (isset($index) && $index >= 0) {
                $usage_oid = '.1.3.6.1.4.1.2636.3.39.1.12.1.1.1.4.'.$index;
                $descr     = 'CPU';
                // No description in the table?
                $usage = $entry['jnxJsSPUMonitoringCPUUsage'];

                discover_processor($valid['processor'], $device, $usage_oid, $index, 'junos', $descr, '1', $usage, null, null);
            }
        }
    }
}

unset($processors_array);
unset($srx_processors_array);
