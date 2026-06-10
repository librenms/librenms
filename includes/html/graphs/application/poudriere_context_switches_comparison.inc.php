<?php

$unit_text = 'switches';

$stats_list = [
    'voluntary-context-switches' => [
        'stat' => 'voluntary-context-switches',
        'descr' => 'Voluntary Context',
    ],
    'involuntary-context-switches' => [
        'stat' => 'involuntary-context-switches',
        'descr' => 'Involuntary Context',
    ],
];

require 'poudriere-common.inc.php';
