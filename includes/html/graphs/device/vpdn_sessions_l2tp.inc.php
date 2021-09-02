<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'vpdn-l2tp');

$stats = ['sessions'];

$i = 0;
foreach ($stats as $stat) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['ds'] = $stat;
}

$colours = 'mixed';

$nototal = 1;
$simple_rrd = 1;

require 'includes/html/graphs/generic_multi_line.inc.php';
