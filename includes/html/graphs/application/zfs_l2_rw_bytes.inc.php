<?php

$name = 'zfs';
$unit_text = 'Bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'L2 Write',
        'ds' => 'l2_write_bytes',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'L2 Read',
        'ds' => 'l2_read_bytes',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
