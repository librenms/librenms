<?php

$unit_text = 'empty_resps';
$descr = 'empty_resps';
$ds = 'empty_resps';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
