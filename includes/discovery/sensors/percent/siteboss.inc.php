<?php

$oid = snmpwalk_cache_multi_oid($device, 'fsStatusTable', [], $pre_cache['siteboss_mibs'], null, '-OQUbs');

foreach ($oid as $index => $entry) {
    if (substr($entry['fsStatusVolumeValueString'], 0, 4) != '-999') {
        discover_sensor(
            $valid['sensor'],
            'percent',
            $device,
            $pre_cache['oid_prefix'] . '.1.6.1.1.7.' . $index,
            $index,
            'siteboss',
            $entry['fsStatusName'],
            100,
            1,
            10,
            20,
            null,
            null,
            $entry['fsStatusVolumePercentLevel']
        );
    }
}

unset($oid);
