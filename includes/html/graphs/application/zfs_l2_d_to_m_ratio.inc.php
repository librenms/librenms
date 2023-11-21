<?php

$name = 'zfs';
$unit_text = 'Ratio';
$colours = 'psychedelic';
$descr = 'L2 Data To Meta';
$ds = 'l2_d_to_m_ratio';

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
