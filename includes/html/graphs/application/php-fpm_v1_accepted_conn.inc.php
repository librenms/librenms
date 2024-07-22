<?php

$descr = 'Accepted Conns';
$stat = 'accepted_conn';
$unit_text = 'Conns/S';

require 'php-fpm-include.php';

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
