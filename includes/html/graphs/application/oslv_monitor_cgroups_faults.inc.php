<?php

$unit_text = 'faults/sec';

$stats_list = [
    'minor-faults' => [
        'stat' => 'pgfault',
        'descr' => 'Minor',
    ],
    'major-faults' => [
        'stat' => 'pgmajfault',
        'descr' => 'Major',
    ],
];

require 'oslv_monitor-common.inc.php';
