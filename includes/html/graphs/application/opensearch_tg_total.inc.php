<?php

$name = 'opensearch';
$unit_text = 'Ops';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tg']);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Gets',
        'ds' => 'tg_total',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
