<?php

$rrd_filename = Rrd::name($device['hostname'], 'sgos_client_connections_idle');

require 'includes/html/graphs/common.inc.php';

$ds = 'client_idle';

$colour_area = '9999cc';
$colour_line = 'ff0000';

$colour_area_max = '9999cc';

$scale_min = '0';

$unit_text = 'Idle Conn';

require 'includes/html/graphs/generic_simplex.inc.php';
