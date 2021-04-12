<?php

$rrd_filename = Rrd::name($device['hostname'], 'bigip_system_client_concurrent_connections');

require 'includes/html/graphs/common.inc.php';

$ds = 'ClientCurConns';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'Active connections';

require 'includes/html/graphs/generic_simplex.inc.php';
