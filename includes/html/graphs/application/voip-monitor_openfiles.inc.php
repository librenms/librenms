<?php

$scale_min = 1000;

require 'includes/html/graphs/common.inc.php';

$voip_monitor_rrd = rrd_name($device['hostname'], ['app', 'voip-monitor', $app['app_id']]);

if (rrdtool_check_rrd_exists($voip_monitor_rrd)) {
    $rrd_filename = $voip_monitor_rrd;
}

$ds = 'openfiles';

$colour_area = 'F0E68C';
$colour_line = 'FF4500';

$colour_area_max = 'FFEE99';

$graph_max = 1000000;

$unit_text = 'Open files';

require 'includes/html/graphs/generic_simplex.inc.php';
