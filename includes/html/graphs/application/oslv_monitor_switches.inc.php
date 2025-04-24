<?php

$unit_text = 'switches/sec';

$stats_list = [
    'voluntary-context-switches' => [
        'stat' => 'voluntary-context-switches',
        'descr' => 'Voluntary',
    ],
    'involuntary-context-switches' => [
        'stat' => 'involuntary-context-switches',
        'descr' => 'Involuntary',
    ],
];

require 'oslv_monitor-common.inc.php';
