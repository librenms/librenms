<?php

$name = 'pwrstatd';
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

if (isset($vars['sn'])) {
    $sn_list = [$vars['sn']];
} else {
    $sn_list = Rrd::getRrdApplicationArrays($device, $app->app_id, $name);
}

$rrd_list = [];

if (! $sn_list) {
    graph_error('No Data to Display', 'No Data');
}

$i = 0;
$j = 0;
while (isset($sn_list[$i])) {
    $sn = $sn_list[$i];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $sn]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        foreach ($rrdArray as $ds => $var) {
            $rrd_list[$j]['filename'] = $rrd_filename;
            $rrd_list[$j]['descr'] = $var['descr'];
            $rrd_list[$j]['ds'] = $ds;
            $j++;
        }
    } else {
        graph_error('No Data file ' . basename($rrd_filename), 'No Data');
    }
    $i++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
