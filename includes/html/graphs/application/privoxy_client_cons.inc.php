<?php

$unit_text = 'client_cons';
$descr = 'client_cons';
$ds = 'client_cons';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
