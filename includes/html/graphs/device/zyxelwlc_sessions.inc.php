<?php

$rrd_filename = Rrd::name($device['hostname'], 'zyxelwlc-sessions');

$ds = 'sessions';

$colour_area = '00000000';
$colour_line = 'cc0000';
$scale_min = '0';

$unit_text = 'Sessions';

require 'includes/html/graphs/generic_simplex.inc.php';
