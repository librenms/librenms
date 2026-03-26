<?php

$name = 'zfs';
$unit_text = 'Errors/Second';
$colours = 'psychedelic';
$descr = 'L2 Errors';
$ds = 'l2_errors';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

require 'includes/html/graphs/generic_stats.inc.php';
