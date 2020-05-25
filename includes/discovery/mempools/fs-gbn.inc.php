<?php
//
// Discovery for FS gbn devices.
//
//

if ($device['os'] == 'fs-gbn') {
    echo 'FS : ';
    //
    // OIDs from GBNPlatformOAM-MIB
    //
    $mempools_array = snmpwalk_cache_oid($device, 'memorySize', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    $mempools_array = snmpwalk_cache_oid($device, 'memoryIdle', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    $mempools_array = snmpwalk_cache_oid($device, 'cpuDescription', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    d_echo($mempools_array);
    foreach ($mempools_array as $index => $entry) {
        //
        // We keep the foreach in case multiple replies are seen here.
        // We could replace it with a condition (if we suppose only 1 reply will ever come) but
        // this would not change the complexity.
        //
        if ($entry['memorySize'] != 0) {
            d_echo($index.' '.$entry['memorySize'].' -> '.$entry['memoryIdle'].' ('.$entry['cpuDescription'].")\n");
            $descr     = $entry['cpuDescription'];
            if (empty($descr)) {
                $descr = "Chassis CPU";
            }
            $descr    .= " Memory";
            $usage     = $entry['memoryIdle'];
            discover_mempool($valid_mempool, $device, $index, 'fs-gbn', $descr, '1', null, null);
        } //end if
    } //end foreach
} //end if
unset($mempools_array);
