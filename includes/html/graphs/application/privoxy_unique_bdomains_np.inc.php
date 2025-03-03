<?php

$unit_text = 'unique_bdomains_np';
$descr = 'unique_bdomains_np';
$ds = 'unique_bdomains_np';

$filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
