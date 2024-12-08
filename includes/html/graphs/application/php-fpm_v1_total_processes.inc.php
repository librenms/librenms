<?php

$descr = 'Total Procs';
$stat = 'total_processes';

require 'php-fpm-include.php';

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
