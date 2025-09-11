<?php

$unit_text = 'COWs/sec';

$stats_list = [
    'copy-on-write-faults' => [
        'stat' => 'copy-on-write-faults',
        'descr' => 'COW Faults',
    ],
];

require 'oslv_monitor-common.inc.php';
