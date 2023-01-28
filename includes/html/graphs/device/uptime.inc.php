<?php

$filename = Rrd::name($device['hostname'], 'uptime');

$ds='uptime';

$colours = 'greens';
$float_precision = 3;

$descr = '';

require 'includes/html/graphs/generic_stats.inc.php';
