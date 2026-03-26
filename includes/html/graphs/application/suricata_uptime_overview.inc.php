<?php

$munge = true;
$name = 'suricata';
$unit_text = 'days';
$ds = 'data';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___uptime']);

if (! Rrd::checkRrdExists($rrd_filename)) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    $ds = 'uptime';
}

require 'includes/html/graphs/generic_stats.inc.php';
