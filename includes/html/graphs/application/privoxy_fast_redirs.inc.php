<?php

$unit_text = 'fast_redirs';
$descr = 'fast_redirs';
$ds = 'fast_redirs';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
