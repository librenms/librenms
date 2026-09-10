<?php

$unit_text = 'resp_504';
$descr = 'resp_504';
$ds = 'resp_504';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
