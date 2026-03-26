<?php

$unit_text = 'empty_resps_reuse';
$descr = 'empty_resps_reuse';
$ds = 'empty_resps_reuse';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
