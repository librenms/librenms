<?php

$unit_text = '(u)secs/sec';

$stats_list = [
    'cpu-time' => [
        'stat' => 'cpu-time',
        'descr' => 'CPU',
    ],
    'system-time' => [
        'stat' => 'system-time',
        'descr' => 'System',
    ],
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
