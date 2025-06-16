<?php

$unit_text = 'empty_resps_new';
$descr = 'empty_resps_new';
$ds = 'empty_resps_new';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
