<?php

$name = 'hv-monitor';
$unit_text = 'Bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vmdisk', $vars['vm'], '__-__', $vars['vmdisk']]);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Allocated',
        'ds' => 'alloc',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'In Use',
        'ds' => 'in_use',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'On Disk',
        'ds' => 'on_disk',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
