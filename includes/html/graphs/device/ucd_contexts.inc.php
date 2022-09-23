<?php

$rrd_filename = Rrd::name($device['hostname'], 'ucd_ssRawContexts');

$ds = 'value';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$scale_min = 1;

$unit_text = 'Switches/s';

require 'includes/html/graphs/generic_simplex.inc.php';
