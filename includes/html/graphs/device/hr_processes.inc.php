<?php

$filename = Rrd::name($device['hostname'], 'hr_processes');

$ds = 'procs';

$unit_text = 'Processes';

$float_precision = 3;

$descr = '';

require 'includes/html/graphs/generic_stats.inc.php';
