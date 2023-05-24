<?php

$unit_text = 'bytes';
$descr = 'mode size';
$ds = 'mode_size';

require 'logsize-common.inc.php';

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
