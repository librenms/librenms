<?php

$unit_text = 'Count';

$stats_list = [
    'http1_0' => [
        'stat' => 'http1_0',
        'descr' => '1.0',
    ],
    'http1_1' => [
        'stat' => 'http1_1',
        'descr' => '1.1',
    ],
    'http2' => [
        'stat' => 'http2',
        'descr' => '2',
    ],
    'http3' => [
        'stat' => 'http3',
        'descr' => '3',
    ],
];

require 'http_access_log_combined-common.inc.php';
