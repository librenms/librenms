<?php

$name = 'zfs';
$unit_text = 'Bytes';
$colours = 'psychedelic';
$descr = 'L2 BufC M Asize';
$ds = 'l2_bufc_m_asize';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

require 'includes/html/graphs/generic_stats.inc.php';
