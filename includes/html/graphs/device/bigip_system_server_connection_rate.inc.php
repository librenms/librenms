<?php

$rrd_filename = Rrd::name($device['hostname'], 'bigip_system_server_connection_rate');

require 'includes/html/graphs/common.inc.php';

$ds = 'ServerTotConns';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'Connection Rate';

require 'includes/html/graphs/generic_simplex.inc.php';
