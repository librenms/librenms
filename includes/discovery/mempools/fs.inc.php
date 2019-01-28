<?php
//
// Discovery for FS devices.
//
// Multiple MIBs are available. We proceed the 2 in sequence, and use a different "mempool type" in order to 
// segregate them in the polling phase. 
// 2 different files are used in the polling phase to comply to the poller logic. 
//

if ($device['os'] == 'fs') {
    echo 'FS : ';
    //
    // First OIDs from GBNPlatformOAM-MIB
    //
    $mempools_array = snmpwalk_cache_oid($device, 'memorySize', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    $mempools_array = snmpwalk_cache_oid($device, 'memoryIdle', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    $mempools_array = snmpwalk_cache_oid($device, 'cpuDescription', $mempools_array, 'GBNPlatformOAM-MIB', 'fs');
    d_echo($mempools_array);
    if (is_array($mempools_array)) {
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
                discover_mempool($valid_mempool, $device, $index, 'fs', $descr, '1', null, null);
            } //end if
        } //end foreach
    } //end if
    //
    //Second OIDs from SWITCH mib
    //
    $mempools_array = snmpwalk_cache_oid($device, 'memTotalReal', [], 'SWITCH', 'fs');
    $mempools_array = snmpwalk_cache_oid($device, 'memTotalFree', $mempools_array, 'SWITCH', 'fs');
    $mempools_array = snmpwalk_cache_oid($device, 'memTotalUsed', $mempools_array, 'SWITCH', 'fs');
    d_echo($mempools_array);
    if (is_array($mempools_array)) {
        foreach ($mempools_array as $index => $entry) {
            if ($entry['memTotalReal'] > 0) {
                d_echo($index.' '.$entry['memTotalReal'].' -> '.$entry['memTotalFree']."\n");
                $descr     = "Chassis";
                $descr    .= " Memory";
                $usage     = $entry['memTotalFree'];
                discover_mempool($valid_mempool, $device, $index, 'fs-switchmib', $descr, '1', null, null);
            } //end if
        } //end foreach
    } //end if
} //end if
unset($mempools_array);
