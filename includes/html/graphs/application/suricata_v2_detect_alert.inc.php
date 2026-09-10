<?php

$name = 'suricata';
$unit_text = 'alerts/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___detect__alert']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___detect__alert']);
}

$rrd_list = [
    'filename' => $rrd_filename,
    'descr' => 'Alerts',
    'ds' => 'data',
];

require 'includes/html/graphs/generic_multi_line.inc.php';
