<?php

$unit_text = 'con_timeouts';
$descr = 'con_timeouts';
$ds = 'con_timeouts';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
