<?php

$name = 'suricata';
$unit_text = 'seconds';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $error_delta_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___error_delta']);
} else {
    $error_delta_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___error_delta']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($error_delta_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $error_delta_rrd_filename,
        'descr' => 'Error Delta',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $error_delta_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
