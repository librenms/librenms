<?php

$unit_text = 'ver_2';
$descr = 'ver_2';
$ds = 'ver_2';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
