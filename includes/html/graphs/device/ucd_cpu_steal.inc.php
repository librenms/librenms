<?php

$filename = Rrd::name($device['hostname'], 'ucd_ssCpuRawSteal');

$ds = 'value';

$unit_text = 'CPU Steal';

$float_precision = 3;

$descr = '';

require 'includes/html/graphs/generic_stats.inc.php';
