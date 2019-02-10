<?php
//
// Discovery for FS-switch devices.
//

if ($device['os'] == 'fs-switch') {
    echo 'FS : ';
    $mempools_array = snmpwalk_cache_oid($device, 'memTotalReal', [], 'SWITCH', 'fs');
    $mempools_array = snmpwalk_cache_oid($device, 'memTotalFree', $mempools_array, 'SWITCH', 'fs');
    $mempools_array = snmpwalk_cache_oid($device, 'memTotalUsed', $mempools_array, 'SWITCH', 'fs');
    d_echo($mempools_array);
    foreach ($mempools_array as $index => $entry) {
        if ($entry['memTotalReal'] > 0) {
            d_echo($index.' '.$entry['memTotalReal'].' -> '.$entry['memTotalFree']."\n");
            $descr     = "Chassis";
            $descr    .= " Memory";
            $usage     = $entry['memTotalFree'];
            discover_mempool($valid_mempool, $device, $index, 'fs-switch', $descr, '1', null, null);
        } //end if
    } //end foreach
} //end if
unset($mempools_array);
