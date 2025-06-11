<?php

$name = 'zfs';
$unit_text = 'percent';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool']]);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Capacity',
        'ds' => 'cap',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
