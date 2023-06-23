<?php

$unit_text = 'resp_5xx_other';
$descr = 'resp_5xx_other';
$ds = 'resp_5xx_other';

$filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
