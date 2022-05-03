<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_translate');

$ds = 'translate';

$colour_area = 'cc9933';
$colour_line = 'ff9933';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Translate';

require 'includes/html/graphs/generic_simplex.inc.php';
