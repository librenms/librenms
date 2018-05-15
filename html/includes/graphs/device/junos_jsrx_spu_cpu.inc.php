<?php

$rrd_filename = rrd_name($device['hostname'], 'junos_jsrx_spu_cpu');

require 'includes/graphs/common.inc.php';

$ds = 'spu_cpu';

$colour_area = '9999cc';
$colour_line = 'ff0000';

$colour_area_max = '9999cc';

$scale_min = '0';

$unit_text = 'SPU CPU%';

require 'includes/graphs/generic_simplex.inc.php';
