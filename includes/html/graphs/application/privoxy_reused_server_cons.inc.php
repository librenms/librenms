<?php

$unit_text = 'reused_server_cons';
$descr = 'reused_server_cons';
$ds = 'reused_server_cons';

$filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
