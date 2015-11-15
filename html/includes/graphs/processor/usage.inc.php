<?php

$scale_min = '0';
$scale_max = '100';

$ds = 'usage';

// FIXME: As far as I can tell, $descr is never mentioned in includes/graphs/generic_simplex.inc.php -- PDG 2015-11-14
$descr = rrdtool_escape(short_hrDeviceDescr($proc['processor_descr']), 28);

$colour_line   = 'cc0000';
$colour_area   = 'FFBBBB';
$colour_minmax = 'c5c5c5';

$graph_max = 1;
$unit_text = 'Usage';

require 'includes/graphs/generic_simplex.inc.php';
