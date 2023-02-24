<?php

$name = 'bind';
$unit_text = 'per second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'cache']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Mem Exhaustion',
        'ds'       => 'crddtme',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TTL Expiration',
        'ds'       => 'crddtte',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
