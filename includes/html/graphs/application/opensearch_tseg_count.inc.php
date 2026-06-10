<?php

$name = 'opensearch';
$unit_text = 'Segments';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tseg']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Segments Count',
        'ds' => 'tseg_count',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
