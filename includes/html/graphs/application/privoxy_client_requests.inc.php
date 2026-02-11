<?php

$unit_text = 'client_requests';
$descr = 'client_requests';
$ds = 'client_requests';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
