<?php

$unit_text = 'resp_502';
$descr = 'resp_502';
$ds = 'resp_502';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
