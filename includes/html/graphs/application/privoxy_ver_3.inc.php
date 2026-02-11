<?php

$unit_text = 'ver_3';
$descr = 'ver_3';
$ds = 'ver_3';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
