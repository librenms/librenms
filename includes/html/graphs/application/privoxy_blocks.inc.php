<?php

$unit_text = 'blocks';
$descr = 'blocks';
$ds = 'blocks';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
