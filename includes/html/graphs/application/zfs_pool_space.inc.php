<?php

$name = 'zfs';
$app_id = $app['app_id'];
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], $vars['pool']]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Size',
        'ds'       => 'size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Allocated',
        'ds'       => 'alloc',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Free',
        'ds'       => 'free',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Expand Size',
        'ds'       => 'expandsz',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
