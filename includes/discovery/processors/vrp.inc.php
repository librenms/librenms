<?php

// Huawei VRP Processors
if ($device['os'] == 'vrp') {
    echo 'Huawei VRP ';
    $processors_array = snmpwalk_cache_multi_oid($device, 'hwEntityCpuUsage', $processors_array, 'HUAWEI-ENTITY-EXTENT-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/huawei');
    $processors_array = snmpwalk_cache_multi_oid($device, 'hwEntityMemSize', $processors_array, 'HUAWEI-ENTITY-EXTENT-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/huawei');
    $processors_array = snmpwalk_cache_multi_oid($device, 'hwEntityBomEnDesc', $processors_array, 'HUAWEI-ENTITY-EXTENT-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/huawei');
    d_echo($processors_array);

    if (is_array($processors_array)) {
        foreach ($processors_array as $index => $entry) {
            if ($entry['hwEntityMemSize'] != 0) {
                d_echo($index.' '.$entry['hwEntityBomEnDesc'].' -> '.$entry['hwEntityCpuUsage'].' -> '.$entry['hwEntityMemSize']."\n");

                $usage_oid = '.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.5.'.$index;
                $descr     = $entry['hwEntityBomEnDesc'];
                $usage     = $entry['hwEntityCpuUsage'];
                if (!strstr($descr, 'No') && !strstr($usage, 'No') && $descr != '') {
                    discover_processor($valid['processor'], $device, $usage_oid, $index, 'vrp', $descr, '1', $usage, null, null);
                }
            } //end if
        } //end foreach
    } //end if
} //end if

unset($processors_array);
