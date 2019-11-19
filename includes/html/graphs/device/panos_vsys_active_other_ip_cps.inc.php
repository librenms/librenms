<?php

$rrd_filename = rrd_name($device['hostname'], 'panos-panVsysActiveOtherIpCps');

$ds = 'new_connections_other_ip';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'New Other IP Connections per Second';

require 'includes/html/graphs/generic_simplex.inc.php';

