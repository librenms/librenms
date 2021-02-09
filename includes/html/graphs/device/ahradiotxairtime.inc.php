<?php

$rrd_filename = rrd_name($device['hostname'], 'ahradiotxairtime');

require 'includes/html/graphs/common.inc.php';

$ds = 'txairtime';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_min = 0;

$unit_text = 'WiFi0 Tx Airtime';

require 'includes/html/graphs/generic_simplex.inc.php';