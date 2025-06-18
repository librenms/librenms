<?php

$unit_text = 'resp_302';
$descr = 'resp_302';
$ds = 'resp_302';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
