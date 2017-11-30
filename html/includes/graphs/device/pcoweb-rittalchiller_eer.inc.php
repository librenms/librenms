<?php

$rrd_filename = rrd_name($device['hostname'], 'pcoweb-rittalchiller_eer');

$ds = 'eer';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'E.E.R.';

require 'includes/graphs/generic_simplex.inc.php';
