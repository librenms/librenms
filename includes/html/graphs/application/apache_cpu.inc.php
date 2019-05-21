<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$apache_rrd = rrd_name($device['hostname'], array('app', 'apache', $app['app_id']));

if (rrdtool_check_rrd_exists($apache_rrd)) {
    $rrd_filename = $apache_rrd;
}

$ds = 'cpu';

$colour_area = 'F0E68C';
$colour_line = 'FF4500';

$colour_area_max = 'FFEE99';

$graph_max = 1;

$unit_text = '% Used';

require 'includes/html/graphs/generic_simplex.inc.php';
