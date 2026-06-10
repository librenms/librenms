<?php

$name = 'hv-monitor';
$unit_text = 'Bytes';
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
        'descr' => 'Allocated',
        'ds' => 'disk_alloc',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'In Use',
        'ds' => 'disk_in_use',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'On Disk',
        'ds' => 'disk_on_disk',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
