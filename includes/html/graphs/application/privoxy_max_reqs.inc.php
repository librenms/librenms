<?php

$unit_text = 'max_reqs';
$descr = 'max_reqs';
$ds = 'max_reqs';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
