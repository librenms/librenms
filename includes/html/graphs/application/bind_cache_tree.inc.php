<?php

$name = 'bind';
$app_id = $app['app_id'];
$unit_text = 'Tree Memory';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app['app_id'], 'cache']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Total',
        'ds'       => 'ctmt',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'In Use',
        'ds'       => 'ctmiu',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Highest In Use',
        'ds'       => 'cthmiu',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
