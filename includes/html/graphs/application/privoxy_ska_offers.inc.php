<?php

$unit_text = 'ska_offers';
$descr = 'ska_offers';
$ds = 'ska_offers';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
