<?php

$unit_text = 'resp_303';
$descr = 'resp_303';
$ds = 'resp_303';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
