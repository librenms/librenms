<?php

$name = 'zfs';
$unit_text = 'Errors/Second';
$colours = 'psychedelic';
$descr = 'L2 Errors';
$ds = 'l2_errors';

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
