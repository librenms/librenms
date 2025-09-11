<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'cipsec_flow');
$ds = 'Tunnels';
$colour_area = '9999cc';
$colour_line = '0000cc';
$colour_area_max = 'aaaaacc';
$scale_min = 0;
$unit_text = 'Active Tunnels';

require 'includes/html/graphs/generic_simplex.inc.php';
