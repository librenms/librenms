<?php

$unit_text = 'con_failures';
$descr = 'con_failures';
$ds = 'con_failures';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
