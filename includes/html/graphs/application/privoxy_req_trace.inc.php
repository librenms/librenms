<?php

$unit_text = 'req_trace';
$descr = 'req_trace';
$ds = 'req_trace';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
