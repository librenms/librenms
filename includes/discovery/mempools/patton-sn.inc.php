<?php

// Patton SmartNode MemPools
if ($device['os'] == 'patton-sn') {
    echo 'Patton SN : ';
    $mempools_array = snmpwalk_cache_multi_oid($device, 'memAllocatedBytes', $mempools_array, 'SMARTNODE-MIB', 'patton');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'memFreeBytes', $mempools_array, 'SMARTNODE-MIB', 'patton');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'memDescr', $mempools_array, 'SMARTNODE-MIB', 'patton');

    d_echo($mempools_array);

    if (is_array($mempools_array)) {
        foreach ($mempools_array as $index => $entry) {
            if ($entry['memAllocatedBytes'] != 0 || $entry['memFreeBytes'] != 0) {
                d_echo($index.' '.$entry['memDescr'].': '.$entry['memAllocatedBytes'].' allocated,  '.$entry['memFreeBytes']." free\n");

                //$usage_oid = '.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.7.'.$index;
                $descr     = $entry['memDescr'];
                $descr    .= " Memory";
                $usage     = ($entry['memAllocatedBytes'] / ($entry['memAllocatedBytes'] + $entry['memFreeBytes']) * 100);
                if (!strstr($descr, 'No') && !strstr($usage, 'No') && $descr != '') {
                    discover_mempool($valid_mempool, $device, $index, 'patton-sn', $descr, '1', null, null);
                }
            } //end if
        } //end foreach
    } //end if
} //end if

unset($mempools_array);
