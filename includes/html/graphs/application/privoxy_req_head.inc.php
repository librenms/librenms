<?php

$unit_text = 'req_head';
$descr = 'req_head';
$ds = 'req_head';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
