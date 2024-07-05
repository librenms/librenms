<?php

$descr = 'Uptime';
$stat = 'start_since';

$munge = true;
$unit_text = 'days';

require 'php-fpm-include.php';

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
