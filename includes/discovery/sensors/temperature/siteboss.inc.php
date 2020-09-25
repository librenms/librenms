<?php

foreach ($pre_cache['esPointTable'] as $index => $entry) {
    if ($entry['esIndexPC'] == 1) {
        discover_sensor(
            $valid['sensor'],
            'temperature',
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
