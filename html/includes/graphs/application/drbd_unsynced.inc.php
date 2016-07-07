<?php

$scale_min = 0;

require 'includes/graphs/common.inc.php';

$drbd_rrd = rrd_name($device['hostname'], array('app', 'drbd', $app['app_instance']));

if (is_file($drbd_rrd)) {
    $rrd_filename = $drbd_rrd;
}

$ds = 'oos';

$colour_area = 'CDEB8B';
$colour_line = '006600';

$colour_area_max = 'FFEE99';

$graph_max  = 1;
$multiplier = 8;

$unit_text = 'Bytes';

require 'includes/graphs/generic_simplex.inc.php';
