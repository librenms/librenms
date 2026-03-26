<?php

$name = 'hv-monitor';
$unit_text = 'Context Switches';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['vm'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vm', $vars['vm']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Involuntary',
        'ds' => 'nivcsw',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Voluntary',
        'ds' => 'nvcsw',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
