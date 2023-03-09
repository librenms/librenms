<?php

$ds = 'frequency';
$unit_text = 'Frequency';
$filename = Rrd::name($device['hostname'], ['app', 'ntp-server', $app->app_id]);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
