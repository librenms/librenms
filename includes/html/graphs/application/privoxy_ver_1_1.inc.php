<?php

$unit_text = 'ver_1_1';
$descr = 'ver_1_1';
$ds = 'ver_1_1';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
