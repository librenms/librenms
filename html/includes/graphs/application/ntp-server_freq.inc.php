<?php

require 'includes/graphs/common.inc.php';

$ds              = 'frequency';
$colour_area     = $config['graph_colours']['pinks'][0].'33';
$colour_line     = $config['graph_colours']['pinks'][0];
$colour_area_max = 'FFEE99';
$graph_max       = 100;
$unit_text       = 'Frequency';
$ntpdserver_rrd  = rrd_name($device['hostname'], array('app', 'ntp-server', $app['app_id']));

if (rrdtool_check_rrd_exists($ntpdserver_rrd)) {
    $rrd_filename = $ntpdserver_rrd;
}

require 'includes/graphs/generic_simplex.inc.php';
