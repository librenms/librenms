<?php

$scale_min = '0';
$scale_max = '100';

$ds = 'ProcessingLoad';

$colour_line   = 'cc0000';
$colour_area   = 'FFBBBB';
$colour_minmax = 'c5c5c5';

$graph_max = 1;
$unit_text = 'Utilization';
$line_text = $components['name'];

require 'includes/html/graphs/generic_simplex.inc.php';
