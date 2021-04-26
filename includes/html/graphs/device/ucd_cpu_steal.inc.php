<?php

$rrd_filename = Rrd::name($device['hostname'], 'ucd_ssCpuRawSteal');

$ds = 'value';

$colour_area = '1111BB';
$colour_line = '0000CC';

$colour_area_max = 'cc9999';

// $graph_max = 1;
$scale_min = 0;

$unit_text = 'CPU Steal';

require 'includes/html/graphs/generic_simplex.inc.php';
