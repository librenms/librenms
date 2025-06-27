<?php

$name = 'opensearch';
$unit_text = 'Failed Ops/Sec';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'ti']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Index Failed',
        'ds' => 'ti_failed',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
