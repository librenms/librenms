<?php

$unit_text = 'bytes';
$descr = 'size diff, -2d';
$ds = '2d_size_diff';

require 'logsize-common.inc.php';

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
