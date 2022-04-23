<?php

$name = 'bind';
$app_id = $app['app_id'];
$unit_text = 'In Hash Table';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app['app_id'], 'adb']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Addresses',
        'ds'       => 'aiht',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Names',
        'ds'       => 'niht',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
