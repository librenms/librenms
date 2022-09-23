<?php

$rrd_filename = Rrd::name($device['hostname'], 'ucd_ssRawInterrupts');

$ds = 'value';

$colour_area = 'EEBBBB';
$colour_line = 'CC0000';

$colour_area_max = 'cc9999';

// $graph_max = 1;
$scale_min = 0;

$unit_text = 'Interrupts/s';

require 'includes/html/graphs/generic_simplex.inc.php';
