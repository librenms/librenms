<?php

$name = 'suricata';
$unit_text = 'VNTAG pkt/s';
$colours = 'psychedelic';
$descr_len = 16;

if (isset($vars['sinstance'])) {
    $decoder__event__vntag__header_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__vntag__header_too_small']);
    $decoder__event__vntag__unknown_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__vntag__unknown_type']);
} else {
    $decoder__event__vntag__header_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__vntag__header_too_small']);
    $decoder__event__vntag__unknown_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__vntag__unknown_type']);
}

if (Rrd::checkRrdExists($decoder__event__vntag__header_too_small_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__event__vntag__header_too_small_rrd_filename,
        'descr' => 'Header Too Small',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__vntag__unknown_type_rrd_filename,
        'descr' => 'Unknown Type',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $decoder__event__vntag__header_too_small_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
