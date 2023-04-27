<?php

$unit_text = 'bytes';
$descr = 'size diff, -1d';
$ds = '1d_size_diff';

require 'logsize-common.inc.php';

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
