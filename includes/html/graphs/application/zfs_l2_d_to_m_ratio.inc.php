<?php

$name = 'zfs';
$unit_text = 'Ratio';
$colours = 'psychedelic';
$descr = 'L2 Data To Meta';
$ds = 'l2_d_to_m_ratio';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

require 'includes/html/graphs/generic_stats.inc.php';
