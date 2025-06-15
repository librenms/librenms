<?php

$name = 'zfs';
$unit_text = 'Bytes';
$colours = 'psychedelic';
$descr = 'L2 MRU Asize';
$ds = 'l2_mru_asize';

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

require 'includes/html/graphs/generic_stats.inc.php';
