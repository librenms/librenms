<?php

$unit_text = 'usecs/sec';

$stats_list = [
    'user_usec' => [
        'stat' => 'user_usec',
        'descr' => 'User',
    ],
    'system_usec' => [
        'stat' => 'system_usec',
        'descr' => 'System',
    ],
    'usage_usec' => [
        'stat' => 'usage_usec',
        'descr' => 'Combined',
    ],
];

require 'oslv_monitor-common.inc.php';
