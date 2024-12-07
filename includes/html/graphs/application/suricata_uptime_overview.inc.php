<?php

$munge = true;
$name = 'suricata';
$unit_text = 'days';

$v1_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$uptime_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___uptime']);

if (Rrd::checkRrdExists($uptime_rrd_filename)) {
    $ds = 'data';
    $filename = $uptime_rrd_filename;
} elseif (Rrd::checkRrdExists($v1_rrd_filename)) {
    $ds = 'uptime';
    $filename = $v1_rrd_filename;
} else {
    d_echo('RRD "' . $uptime_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
