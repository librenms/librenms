<?php

$name = 'nextcloud';
$unit_text = 'enabled';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'encryption_enabled']);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'encryption',
        'ds' => 'data',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
