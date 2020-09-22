<?php

$name = 'zfs';
$app_id = $app['app_id'];
$unit_text = 'percent';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = rrd_name($device['hostname'], ['app', $name, $app['app_id'], $vars['pool']]);

$rrd_list = [];
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Capacity',
        'ds'       => 'cap',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
