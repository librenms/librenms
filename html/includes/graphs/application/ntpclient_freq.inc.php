<?php

require 'includes/graphs/common.inc.php';

$scale_min       = 0;
$ds              = 'frequency';
$colour_area     = 'F6F6F6';
$colour_line     = 'B3D0DB';
$colour_area_max = 'FFEE99';
$graph_max       = 100;
$unit_text       = 'Frequency';
$ntpclient_rrd   = rrd_name($device['hostname'], array('app', 'ntpclient', $app['app_id']));

if (is_file($ntpclient_rrd)) {
    $rrd_filename = $ntpclient_rrd;
}

require 'includes/graphs/generic_simplex.inc.php';
