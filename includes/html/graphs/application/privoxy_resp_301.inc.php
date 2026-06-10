<?php

$unit_text = 'resp_301';
$descr = 'resp_301';
$ds = 'resp_301';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
