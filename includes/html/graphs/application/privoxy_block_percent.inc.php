<?php

$unit_text = 'block_percent';
$descr = 'block_percent';
$ds = 'block_percent';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
