<?php

$unit_text = 'faults/sec';

$stats_list = [
    'copy-on-write-faults' => [
        'stat' => 'copy-on-write-faults',
        'descr' => 'COW Faults',
    ],
];

require 'oslv_monitor-common.inc.php';
