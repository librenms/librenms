<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $capture__kernel_ifdrops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___capture__kernel_ifdrops']);
    $capture__kernel_drops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___capture__kernel_drops']);
} else {
    $capture__kernel_ifdrops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___capture__kernel_ifdrops']);
    $capture__kernel_drops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___capture__kernel_drops']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($capture__kernel_ifdrops_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $capture__kernel_ifdrops_rrd_filename,
        'descr' => 'Kernel IF Drops',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $capture__kernel_ifdrops_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($capture__kernel_drops_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $capture__kernel_drops_rrd_filename,
        'descr' => 'Kernel Drops',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $capture__kernel_drops_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
