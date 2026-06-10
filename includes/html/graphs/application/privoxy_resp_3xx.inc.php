<?php

$unit_text = 'resp_3xx';
$descr = 'resp_3xx';
$ds = 'resp_3xx';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
