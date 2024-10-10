<?php

$unit_text = 'faults/sec';

$stats_list = [
    'minor-faults' => [
        'stat' => 'minor-faults',
        'descr' => 'Minor',
    ],
    'major-faults' => [
        'stat' => 'major-faults',
        'descr' => 'Major',
    ],
];

require 'oslv_monitor-common.inc.php';
