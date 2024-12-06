<?php

$descr = 'Max Children Reached';
$stat = 'max_children_reached';
$unit_text = 'Per Second';

require 'php-fpm-include.php';

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
