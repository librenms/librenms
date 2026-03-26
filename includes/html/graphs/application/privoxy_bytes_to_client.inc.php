<?php

$unit_text = 'bytes_to_client';
$descr = 'bytes_to_client';
$ds = 'bytes_to_client';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
