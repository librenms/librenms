<?php

$ds = 'stability';
$unit_text = 'PPM';
$graph_params->vertical_label = 'PPM';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp-client', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
