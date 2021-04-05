<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$apache_rrd = Rrd::name($device['hostname'], ['app', 'apache', $app['app_id']]);

if (Rrd::checkRrdExists($apache_rrd)) {
    $rrd_filename = $apache_rrd;
}

$ds = 'kbyte';

$colour_area = 'CDEB8B';
$colour_line = '006600';

$colour_area_max = 'FFEE99';

$graph_max = 1;
$multiplier = 8;

$unit_text = 'Kbps';

require 'includes/html/graphs/generic_simplex.inc.php';
