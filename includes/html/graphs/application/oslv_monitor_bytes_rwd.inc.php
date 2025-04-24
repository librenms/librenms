<?php

$unit_text = 'bytes/sec';

$stats_list = [
    'rbytes' => [
        'stat' => 'rbytes',
        'descr' => 'Read',
    ],
    'wbytes' => [
        'stat' => 'wbytes',
        'descr' => 'Write',
    ],
    'dbytes' => [
        'stat' => 'dbytes',
        'descr' => 'Discard',
    ],
];

require 'oslv_monitor-common.inc.php';
