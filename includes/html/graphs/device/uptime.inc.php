<?php

$scale_min = 0;

$filename = Rrd::name($device['hostname'], 'uptime');

$ds = 'uptime';

$colours = 'greens';
$float_precision = 3;

$descr = 'Uptime';

$munge = true;

$unit_text = 'Days';

require 'includes/html/graphs/generic_stats.inc.php';
