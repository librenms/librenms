<?php

$descr = 'Slow Requests';
$stat = 'slow_requests';
$unit_text = 'Requests/S';

require 'php-fpm-include.php';

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
