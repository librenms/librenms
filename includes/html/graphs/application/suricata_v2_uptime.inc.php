<?php

$munge = true;
$name = 'suricata';
$unit_text = 'days';

if (isset($vars['sinstance'])) {
    $uptime_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___uptime']);
} else {
    $uptime_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___uptime']);
}

if (Rrd::checkRrdExists($uptime_rrd_filename)) {
    $ds = 'data';
    $filename = $uptime_rrd_filename;
} else {
    d_echo('RRD "' . $uptime_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
