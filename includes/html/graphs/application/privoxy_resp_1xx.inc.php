<?php

$unit_text = 'resp_1xx';
$descr = 'resp_1xx';
$ds = 'resp_1xx';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
