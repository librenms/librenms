<?php

$unit_text = 'req_connect';
$descr = 'req_connect';
$ds = 'req_connect';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
