<?php

$filename = Rrd::name($device['hostname'], 'ucd_ssCpuRawWait');

$ds = 'value';

$unit_text = 'IO Wait';

$colours = 'blues';
$float_precision = 3;

$descr = '';

require 'includes/html/graphs/generic_stats.inc.php';
