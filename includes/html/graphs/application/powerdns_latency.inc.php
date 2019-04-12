<?php

require 'includes/html/graphs/common.inc.php';

$scale_min       = 0;
$ds              = 'latency';
$colour_area     = 'F6F6F6';
$colour_line     = 'B3D0DB';
$colour_area_max = 'FFEE99';
$graph_max       = 100;
$unit_text       = 'Latency';
$powerdns_rrd    = rrd_name($device['hostname'], array('app', 'powerdns', $app['app_id']));

if (rrdtool_check_rrd_exists($powerdns_rrd)) {
    $rrd_filename = $powerdns_rrd;
}

require 'includes/html/graphs/generic_simplex.inc.php';
