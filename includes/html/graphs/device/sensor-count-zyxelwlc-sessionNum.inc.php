<?php

$rrd_filename = Rrd::name($device['hostname'], 'sensor-count-zyxelwlc-sessionNum.0');

$ds = 'sensor';

$colour_area = 'cc000000';
$colour_line = 'cc0000';
$scale_min = '0';

$unit_text = 'Active Sessions';

require 'includes/html/graphs/generic_simplex.inc.php';
