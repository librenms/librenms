<?php

$ds = 'frequency';
$unit_text = 'PPM';
$graph_params->vertical_label = 'PPM';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp-server', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
