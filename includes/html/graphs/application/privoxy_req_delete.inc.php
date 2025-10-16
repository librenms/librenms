<?php

$unit_text = 'req_delete';
$descr = 'req_delete';
$ds = 'req_delete';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
