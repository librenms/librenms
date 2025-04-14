<?php

$rrd_filename = Rrd::name($device['hostname'], 'sensor-count-asa-currentInUseConnections');


require 'includes/html/graphs/common.inc.php';

$ds = 'sensor';

$colour_area = 'cc000000';
$colour_line = 'cc0000';
$scale_min = '0';

$unit_text = 'Sessions';

require 'includes/html/graphs/generic_simplex.inc.php';

