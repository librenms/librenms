<?php

$name = 'suricata';
$unit_text = 'pkgs/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $tcp__rst_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__rst']);
} else {
    $tcp__rst_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__rst']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__rst_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__rst_rrd_filename,
        'descr' => 'TCP Rst',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__rst_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
