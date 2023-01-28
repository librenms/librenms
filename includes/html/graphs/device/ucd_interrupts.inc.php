<?php

$rrd_filename = Rrd::name($device['hostname'], 'ucd_ssRawInterrupts');

$ds = 'value';

$unit_text = 'Interrupts/s';

$colours = 'oranges';
$float_precision = 3;

$filename = $rrd_filename;
$descr = '';

require 'includes/html/graphs/generic_stats.inc.php';
