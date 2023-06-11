<?php

$scale_min = 0;

$filename = Rrd::name($device['hostname'], 'uptime');

$ds = 'uptime';

$colours = 'greens';
$float_precision = 3;

$descr = 'Uptime';

$munge=\LibreNMS\Config::get('graph_uptime_days');

$unit_text='Days';
if (!$munge) {
    $unit_text='Seconds';
}

require 'includes/html/graphs/generic_stats.inc.php';
