<?php

$name = 'opensearch';
$unit_text = 'Bytes';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'trc']);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Request Cache',
        'ds' => 'trc_size',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
