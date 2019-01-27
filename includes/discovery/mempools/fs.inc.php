<?php

if ($device['os'] == 'fs') {
    echo 'FS : ';
    $mempools_array = snmpwalk_cache_multi_oid($device, 'memorySize', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'memoryIdle', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'cpuDescription', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    d_echo($mempools_array);

    if (is_array($mempools_array)) {
        foreach ($mempools_array as $index => $entry) {
            if ($entry['memorySize'] != 0) {
                d_echo($index.' '.$entry['memorySize'].' -> '.$entry['memoryIdle'].' ('.$entry['cpuDescription'].")\n");

                $descr     = $entry['cpuDescription'];
                if (empty($descr)) {
                    $descr = "Chassis CPU";
                }
                $descr    .= " Memory";
                $usage     = $entry['memoryIdle'];
                discover_mempool($valid_mempool, $device, $index, 'fs', $descr, '-1', null, null);
            } //end if
        } //end foreach
    } //end if
} //end if

unset($mempools_array);
