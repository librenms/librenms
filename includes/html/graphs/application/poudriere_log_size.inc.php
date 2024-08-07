<?php

$unit_text = 'bytes';

$stats_list = [
    'log_size_done' => [
        'stat' => 'log_size_done',
        'descr' => 'done',
    ],
    'log_size_latest' => [
        'stat' => 'log_size_latest',
        'descr' => 'latest',
    ],
    'log_size_per_package' => [
        'stat' => 'log_size_per_package',
        'descr' => 'per_package',
    ],
];

require 'poudriere-common.inc.php';
