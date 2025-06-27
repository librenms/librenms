<?php

$unit_text = 'req_get';
$descr = 'req_get';
$ds = 'req_get';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
