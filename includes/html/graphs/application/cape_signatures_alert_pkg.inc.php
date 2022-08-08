<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Anti Issues Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$packages = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'cape');

$rrd_list = [];
$packages_int = 0;
while(isset($packages[$packages_int])) {
    $label=$packages[$packages_int];
    $label=preg_filter('/^pkg\-/', '', $label);
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'],'pkg',$label]);
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => $label,
        'ds'       => 'signatures_alert',
    ];

    $packages_int++;
}

if (sizeof($rrd_list)) {
    d_echo('No relevant package RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
