<?php

$rrd_filename = Rrd::name($device['hostname'], 'sgos_client_connections_active');

require 'includes/html/graphs/common.inc.php';

$ds = 'client_conn_active';

$colour_area = '9999cc';
$colour_line = 'ff0000';

$colour_area_max = '9999cc';

$scale_min = '0';

$unit_text = 'Active Conn';

require 'includes/html/graphs/generic_simplex.inc.php';
