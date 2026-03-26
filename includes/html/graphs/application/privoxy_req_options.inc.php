<?php

$unit_text = 'req_options';
$descr = 'req_options';
$ds = 'req_options';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
