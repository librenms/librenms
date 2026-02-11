<?php

$name = 'opensearch';
$unit_text = 'Size';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tqc']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'QC Size',
        'ds' => 'tqc_cache_size',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
