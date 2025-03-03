<?php

$scale_min = 0;
$scale_max = 1;

require 'includes/html/graphs/common.inc.php';

$voip_monitor_rrd = Rrd::name($device['hostname'], ['app', 'voip-monitor', $app->app_id]);

if (Rrd::checkRrdExists($voip_monitor_rrd)) {
    $rrd_filename = $voip_monitor_rrd;
}

$ds = 'cpu';

$colour_area = 'F0E68C';
$colour_line = 'FF4500';

$colour_area_max = 'FFEE99';

$graph_max = 100;

$unit_text = '% Used';

require 'includes/html/graphs/generic_simplex.inc.php';
