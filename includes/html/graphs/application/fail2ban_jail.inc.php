<?php

$unit_text = 'Banned IPs';
$descr = 'Banned';
$ds = 'banned';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id, $vars['jail']]);

require 'includes/html/graphs/generic_stats.inc.php';
