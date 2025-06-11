<?php

$name = 'opensearch';
$unit_text = 'Seconds';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'ttl']);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Last Mod Age',
        'ds' => 'ttl_last_mod_age',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
