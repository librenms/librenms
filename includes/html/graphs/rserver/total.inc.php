<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$graph_max = 1;

$ds = 'RserverTotalConns';

$colour_area = 'B0C4DE';
$colour_line = '191970';

$colour_area_max = 'FFEE99';

$nototal = 1;
$unit_text = 'Conns';

require 'includes/html/graphs/generic_simplex.inc.php';
