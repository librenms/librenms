<?php

$unit_text = 'bytes_to_client';
$descr = 'bytes_to_client';
$ds = 'bytes_to_client';

$filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
