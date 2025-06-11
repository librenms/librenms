<?php

$name = 'opensearch';
$unit_text = 'Milliseconds';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'c']);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Task Max In Time',
        'ds' => 'c_task_max_in_time',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
