<?php

$unit_text = 'resp_503';
$descr = 'resp_503';
$ds = 'resp_503';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
