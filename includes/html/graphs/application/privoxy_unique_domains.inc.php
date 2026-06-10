<?php

$unit_text = 'unique_domains';
$descr = 'unique_domains';
$ds = 'unique_domains';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
