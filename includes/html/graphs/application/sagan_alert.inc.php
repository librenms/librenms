<?php

$name = 'sagan';
$unit_text = 'Status';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Alert',
        'ds' => 'alert',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
