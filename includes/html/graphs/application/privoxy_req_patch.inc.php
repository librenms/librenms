<?php

$unit_text = 'req_patch';
$descr = 'req_patch';
$ds = 'req_patch';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
