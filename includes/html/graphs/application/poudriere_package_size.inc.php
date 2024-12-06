<?php

$unit_text = 'bytes';

$stats_list = [
    'package_size_all' => [
        'stat' => 'package_size_all',
        'descr' => 'all',
    ],
    'package_size_building' => [
        'stat' => 'package_size_building',
        'descr' => 'building',
    ],
    'package_size_latest' => [
        'stat' => 'package_size_latest',
        'descr' => 'latest',
    ],
];

require 'poudriere-common.inc.php';
