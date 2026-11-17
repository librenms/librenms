<?php

$rrd_list = [];
if ($rrd_filename) {
    foreach (['tcp', 'udp', 'icmp', 'other'] as $flowtype) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $flowtype,
            'ds' => $dsprefix . $flowtype,
        ];
    }
}

// set a multiplier which in turn will create a CDEF if this var is set
if ($dsprefix == 'traffic_') {
    $multiplier = '8';
}

$colours = 'blues';
$nototal = 0;
$units = '';
$unit_text = $dsdescr;
$scale_min = '0';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
