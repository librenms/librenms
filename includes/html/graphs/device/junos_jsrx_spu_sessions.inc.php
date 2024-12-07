<?php

$rrd_filename = Rrd::name($device['hostname'], 'junos_jsrx_spu_sessions');

require 'includes/html/graphs/common.inc.php';

$ds = 'spu_flow_sessions';

$colour_area = '9999cc';
$colour_line = 'ff0000';

$colour_area_max = '9999cc';

$scale_min = '0';

$unit_text = 'SPU Flows';

require 'includes/html/graphs/generic_simplex.inc.php';
