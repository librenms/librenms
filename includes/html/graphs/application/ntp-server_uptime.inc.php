<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$ds        = 'uptime';
$colour_area = \LibreNMS\Config::get('graph_colours.purples.0') . '33';
$colour_line = \LibreNMS\Config::get('graph_colours.purples.0');
$colour_area_max = 'FFEE99';
$graph_max       = 0;
$unit_text       = 'Seconds';
$ntpdserver_rrd  = rrd_name($device['hostname'], array('app', 'ntp-server', $app['app_id']));

if (rrdtool_check_rrd_exists($ntpdserver_rrd)) {
    $rrd_filename = $ntpdserver_rrd;
}

require 'includes/html/graphs/generic_simplex.inc.php';
