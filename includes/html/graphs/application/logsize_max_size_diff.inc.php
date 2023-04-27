<?php

$unit_text = 'bytes';
$descr = 'max size diff';
$ds = 'max_size_diff';

require 'logsize-common.inc.php';

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
