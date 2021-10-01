<?php

require 'includes/html/graphs/common.inc.php';

$ds = 'frequency';
$colour_area = \LibreNMS\Config::get('graph_colours.pinks.0') . '33';
$colour_line = \LibreNMS\Config::get('graph_colours.pinks.0');
$colour_area_max = 'FFEE99';
$graph_max = 100;
$unit_text = 'Frequency';
$ntpdserver_rrd = Rrd::name($device['hostname'], ['app', 'ntp-server', $app['app_id']]);

if (Rrd::checkRrdExists($ntpdserver_rrd)) {
    $rrd_filename = $ntpdserver_rrd;
}

require 'includes/html/graphs/generic_simplex.inc.php';
