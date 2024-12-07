<?php

$rrd_filename = Rrd::name($device['hostname'], 'sgos_server_connections');

require 'includes/html/graphs/common.inc.php';

$ds = 'server_conn';

$colour_area = '9999cc';
$colour_line = 'ff0000';

$colour_area_max = '9999cc';

$scale_min = '0';

$unit_text = 'Server Conn';

require 'includes/html/graphs/generic_simplex.inc.php';
