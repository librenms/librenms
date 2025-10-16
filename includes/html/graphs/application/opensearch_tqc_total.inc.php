<?php

$name = 'opensearch';
$unit_text = 'Cached Queries';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tqc']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Queries',
        'ds' => 'tqc_total',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
