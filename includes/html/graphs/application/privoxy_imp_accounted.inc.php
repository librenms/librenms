<?php

$unit_text = 'imp_accounted';
$descr = 'imp_accounted';
$ds = 'imp_accounted';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
