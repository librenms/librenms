<?php

$name = 'nvidia';
$app_id = $app['app_id'];
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$app_id = $app['app_id'];

$int = 0;
$rrd_list = [];
$rrd_filename = Rrd::name($device['hostname'], ['app', $app['app_type'], $app['app_id'], $int]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    echo "file missing: $rrd_filename";
}

while (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'GPU ' . $int,
        'ds'       => $rrdVar,
    ];

    $int++;
    $rrd_filename = Rrd::name($device['hostname'], ['app', $app['app_type'], $app['app_id'], $int]);
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
