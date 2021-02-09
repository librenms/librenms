<?php

$rrd_filename = rrd_name($device['hostname'], 'ahradiorxairtime');

require 'includes/html/graphs/common.inc.php';

$ds = 'rxairtime';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_min = 0;

$unit_text = 'WiFi0 Rx Airtime';

require 'includes/html/graphs/generic_simplex.inc.php';