<?php

$ds = 'offset';
$unit_text = 'Seconds';
$units = 's';
$munge = true;
$munge_opts = '1000,/'; // values are stored in milliseconds, display them as seconds
$graph_params->vertical_label = 'Seconds';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp-client', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
