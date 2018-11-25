<?php

$rrd_filename = rrd_name($device['hostname'], array($type, $subtype));

require 'includes/graphs/common.inc.php';

$scale_min = 0;
$graph_max = 1;
$unit_text = $unit;

$ds        = 'oid_value';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

require 'includes/graphs/generic_simplex.inc.php';
