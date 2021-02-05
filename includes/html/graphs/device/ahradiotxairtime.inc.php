<?php

$rrd_filename = rrd_name($device['hostname'], 'ahradiotxairtime');

require 'includes/graphs/common.inc.php';

$ds = 'ahradiotxairtime';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_min = 0;

$unit_text = 'WiFi0 Tx Airtime';