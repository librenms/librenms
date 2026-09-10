<?php

$unit_text = 'resp_451';
$descr = 'resp_451';
$ds = 'resp_451';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
