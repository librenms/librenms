<?php

$rrd_filename = Rrd::name($device['hostname'], 'barracuda_firewall_sessions');

require 'includes/html/graphs/common.inc.php';

$ds = 'fw_sessions';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

//$graph_max = 1;

$unit_text = 'Sessions';

require 'includes/html/graphs/generic_simplex.inc.php';
