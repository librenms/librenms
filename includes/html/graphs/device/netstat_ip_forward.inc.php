<?php

$rrd_filename = Rrd::name($device['hostname'], 'netstats-ip_forward');

$stats = ['ipCidrRouteNumber' => []];

$i = 0;
foreach ($stats as $stat => $array) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = str_replace('ip', '', $stat);
    $rrd_list[$i]['ds'] = $stat;
}

$colours = 'mixed';

$scale_min = '0';
$nototal = 1;
$simple_rrd = true;

require 'includes/html/graphs/generic_multi_line.inc.php';
