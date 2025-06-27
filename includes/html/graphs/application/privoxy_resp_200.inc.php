<?php

$unit_text = 'resp_200';
$descr = 'resp_200';
$ds = 'resp_200';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
