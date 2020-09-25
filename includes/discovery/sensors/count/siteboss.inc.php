<?php 

foreach ($pre_cache['esPointTable'] as $index => $entry) {
    if ($entry['esIndexPC'] == 5 && $entry['esPointName'] != 'unnamed') {
    
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            $pre_cache['oid_prefix'] . '.1.1.1.1.7.' . $index,
            $index,
            'siteboss',
            $entry['esPointName'],
            1,
            1,
            null,
            null,
            null,
            null,
            $entry['esPointValueStr']
        );
    }
}
