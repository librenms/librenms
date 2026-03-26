<?php

$name = 'zfs';
$unit_text = 'Accesses/Second';
$colours = 'psychedelic';
$descr = 'L2 Accesses';
$ds = 'l2_access_total';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

require 'includes/html/graphs/generic_stats.inc.php';
