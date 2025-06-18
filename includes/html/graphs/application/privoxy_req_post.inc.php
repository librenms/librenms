<?php

$unit_text = 'req_post';
$descr = 'req_post';
$ds = 'req_post';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
