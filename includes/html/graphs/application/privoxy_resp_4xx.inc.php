<?php

$unit_text = 'resp_4xx';
$descr = 'resp_4xx';
$ds = 'resp_4xx';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
