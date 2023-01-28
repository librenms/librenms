<?php

$filename = Rrd::name($device['hostname'], 'ucd_ssRawContexts');

$ds = 'value';

$unit_text = 'Switches/s';

$colours = 'blues';
$float_precision = 3;

$descr = '';

require 'includes/html/graphs/generic_stats.inc.php';
