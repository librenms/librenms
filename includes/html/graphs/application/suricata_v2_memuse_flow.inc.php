<?php

$name = 'suricata';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $flow__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__memuse']);
    $flow__memcap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__memcap']);
} else {
    $flow__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__memuse']);
    $flow__memcap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__memcap']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($flow__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__memuse_rrd_filename,
        'descr' => 'Flow Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $flow__memuse_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($flow__memcap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__memcap_rrd_filename,
        'descr' => 'Flow Memcap',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $flow__memcap_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
