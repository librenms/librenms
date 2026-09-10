<?php

$unit_text = 'resp_403';
$descr = 'resp_403';
$ds = 'resp_403';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
