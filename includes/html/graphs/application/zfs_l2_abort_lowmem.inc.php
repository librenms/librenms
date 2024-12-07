<?php

$name = 'zfs';
$unit_text = 'Instances/Second';
$colours = 'psychedelic';
$descr = 'L2 Abort Lowmem';
$ds = 'l2_abort_lowmem';

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
