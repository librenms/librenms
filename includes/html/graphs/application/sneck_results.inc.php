<?php

$name = 'sneck';
$unit_text = 'Results';
$colours = 'psychedelic';
$dostack = 0;
$descr_len = 10;
$addarea = 0;
$transparency = 15;
$float_precision = 0;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'OK',
        'ds' => 'ok',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Warning',
        'ds' => 'warning',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Critical',
        'ds' => 'critical',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Unknown',
        'ds' => 'unknown',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Errored',
        'ds' => 'errored',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
