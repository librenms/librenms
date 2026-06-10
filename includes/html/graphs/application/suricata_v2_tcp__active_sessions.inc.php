<?php

$name = 'suricata';
$unit_text = 'sessions';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $tcp__active_sessions_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__active_sessions']);
} else {
    $tcp__active_sessions_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__active_sessions']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__active_sessions_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__active_sessions_rrd_filename,
        'descr' => 'TCP Active',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__active_sessions_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
