<?php

$name = 'zfs';
$unit_text = '% of Hits';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Anon Used',
        'ds' => 'anon_hits_per',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Most Recent',
        'ds' => 'mru_per',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Most Frequent',
        'ds' => 'mfu_per',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'MRU Ghost',
        'ds' => 'mru_ghost_per',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'MFU Ghost',
        'ds' => 'mfu_ghost_per',
    ]
];

require 'includes/html/graphs/generic_multi_line.inc.php';
