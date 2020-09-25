<?php

foreach ($pre_cache['esPointTable'] as $index => $entry) {
    if ($entry['esIndexPC'] == 3) {
        discover_sensor(
            $valid['sensor'],
            'humidity',
            $device,
            $pre_cache['oid_prefix'] . '.1.1.1.1.6.' . $index,
            $index,
            'siteboss',
            $entry['esPointName'],
            1,
            1,
            null,
            null,
            null,
            null,
            $entry['esPointValueInt']
        );
    }
}
