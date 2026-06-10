<?php

$unit_text = 'crunches';
$descr = 'crunches';
$ds = 'crunches';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
