<?php

$unit_text = 'resp_5xx';
$descr = 'resp_5xx';
$ds = 'resp_5xx';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
