<?php

$name = 'zfs';
$unit_text = 'Accesses/Second';
$colours = 'psychedelic';
$descr = 'L2 Accesses';
$ds = 'l2_access_total';

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
