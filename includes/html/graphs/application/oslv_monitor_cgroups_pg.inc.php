<?php

$unit_text = 'per second';

$stats_list = [
    'pgactivate' => [
        'stat' => 'pgactivate',
        'descr' => 'pgactivate',
    ],
    'pgdeactivate' => [
        'stat' => 'pgdeactivate',
        'descr' => 'pgdeactivate',
    ],
    'pglazyfree' => [
        'stat' => 'pglazyfree',
        'descr' => 'pglazyfree',
    ],
    'pglazyfreed' => [
        'stat' => 'pglazyfreed',
        'descr' => 'pglazyfreed',
    ],
    'pgrefill' => [
        'stat' => 'pgrefill',
        'descr' => 'pgrefill',
    ],
    'pgscan' => [
        'stat' => 'pgscan',
        'descr' => 'pgscan',
    ],
    'pgscan_direct' => [
        'stat' => 'pgscan_direct',
        'descr' => 'pgscan_direct',
    ],
    'pgscan_khugepaged' => [
        'stat' => 'pgscan_khugepaged',
        'descr' => 'pgscan_khugepaged',
    ],
    'pgscan_kswapd' => [
        'stat' => 'pgscan_kswapd',
        'descr' => 'pgscan_kswapd',
    ],
    'pgsteal' => [
        'stat' => 'pgsteal',
        'descr' => 'pgsteal',
    ],
    'pgsteal_direct' => [
        'stat' => 'pgsteal_direct',
        'descr' => 'pgsteal_direct',
    ],
    'pgsteal_khugepaged' => [
        'stat' => 'pgsteal_khugepaged',
        'descr' => 'pgsteal_khugepaged',
    ],
    'pgsteal_kswapd' => [
        'stat' => 'pgsteal_kswapd',
        'descr' => 'pgsteal_kswapd',
    ],
];

require 'oslv_monitor-common.inc.php';
