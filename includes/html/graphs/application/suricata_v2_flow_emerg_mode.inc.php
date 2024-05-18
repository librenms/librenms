<?php

$name = 'suricata';
$unit_text = 'events';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $flow__emerg_mode_entered_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__emerg_mode_entered']);
    $flow__emerg_mode_over_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__emerg_mode_over']);
} else {
    $flow__emerg_mode_entered_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__emerg_mode_entered']);
    $flow__emerg_mode_over_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__emerg_mode_over']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($flow__emerg_mode_entered_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__emerg_mode_entered_rrd_filename,
        'descr' => 'Emerg Mode Entrd',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $flow__emerg_mode_entered_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($flow__emerg_mode_over_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__emerg_mode_over_rrd_filename,
        'descr' => 'Emerg Mode Over',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $flow__emerg_mode_over_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
