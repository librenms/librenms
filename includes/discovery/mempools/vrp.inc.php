<?php

// Huawei VRP  mempools
if ($device['os'] == 'vrp') {
    echo 'VRP : ';
    $mempools_array = snmpwalk_cache_multi_oid($device, 'hwEntityMemUsage', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'hwEntityMemSize', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'hwEntityBomEnDesc', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'hwEntityMemSizeMega', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'hwEntityBoardType', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
    $mempools_array = snmpwalk_cache_multi_oid($device, 'entPhysicalName', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');

    d_echo($mempools_array);

    if (is_array($mempools_array)) {
        foreach ($mempools_array as $index => $entry) {
            if ($entry['hwEntityMemSize'] != 0 || $entry['hwEntityMemSizeMega'] != 0) {
                d_echo($index.' '.$entry['hwEntityBomEnDesc'].' -> '.$entry['hwEntityMemUsage'].' -> '.$entry['hwEntityMemSize']."\n");

                $usage_oid = '.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.7.'.$index;
                $descr     = $entry['entPhysicalName'];
                if (empty($descr)) {
                    $descr = $entry['hwEntityBomEnDesc'];
                }
                $descr    .= " Memory";
                $usage     = $entry['hwEntityMemUsage'];
                if (!strstr($descr, 'No') && !strstr($usage, 'No') && $descr != '') {
                    discover_mempool($valid_mempool, $device, $index, 'vrp', $descr, '1', null, null);
                }
            } //end if
        } //end foreach
    } //end if
} //end if

unset($mempools_array);
