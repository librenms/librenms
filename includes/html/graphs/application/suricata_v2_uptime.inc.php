<?php

$name = 'suricata';
$unit_text = 'seconds';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $uptime_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___uptime']);
} else {
    $uptime_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___uptime']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($uptime_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $uptime_rrd_filename,
        'descr' => 'Uptime',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $uptime_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
