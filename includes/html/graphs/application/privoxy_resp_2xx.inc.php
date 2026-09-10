<?php

$unit_text = 'resp_2xx';
$descr = 'resp_2xx';
$ds = 'resp_2xx';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
