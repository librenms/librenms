<?php

$name = 'opensearch';
$unit_text = 'Evictions';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tqc']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'QC Evictions',
        'ds' => 'tqc_evictions',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
