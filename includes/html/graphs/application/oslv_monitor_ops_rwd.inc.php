<?php

$unit_text = 'ops/sec';

$stats_list = [
    'rios' => [
        'stat' => 'rios',
        'descr' => 'Read',
    ],
    'wios' => [
        'stat' => 'wios',
        'descr' => 'Write',
    ],
    'dios' => [
        'stat' => 'dios',
        'descr' => 'Discard',
    ],
];

require 'oslv_monitor-common.inc.php';
