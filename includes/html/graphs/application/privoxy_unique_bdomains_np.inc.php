<?php

$unit_text = 'unique_bdomains_np';
$descr = 'unique_bdomains_np';
$ds = 'unique_bdomains_np';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
