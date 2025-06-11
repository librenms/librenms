<?php

$name = 'mojo_cape_submit';
$unit_text = 'Bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

if (isset($vars['slug'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'slugs___-___' . $vars['slug']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Max',
        'ds' => 'size_max',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Mean',
        'ds' => 'size_mean',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Median',
        'ds' => 'size_median',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Mode',
        'ds' => 'size_mode',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Min',
        'ds' => 'size_min',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
