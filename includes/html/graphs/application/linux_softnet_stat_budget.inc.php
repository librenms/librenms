<?php

$name = 'linux_softnet_stat';
$unit_text = 'Budget Value';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Budget',
        'ds' => 'budget',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Budget usecs',
        'ds' => 'budget_usecs',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
