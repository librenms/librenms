<?php

$unit_text = 'resp_404';
$descr = 'resp_404';
$ds = 'resp_404';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
