<?php

$unit_text = 'unique_bdomains';
$descr = 'unique_bdomains';
$ds = 'unique_bdomains';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
