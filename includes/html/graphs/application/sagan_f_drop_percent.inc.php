<?php

$name = 'sagan';
$app_id = $app['app_id'];
$unit_text = 'Percent';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['pool'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], $vars['pool']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id']]);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Flow Drop%',
        'ds'       => 'f_drop_percent',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
