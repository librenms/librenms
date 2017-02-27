<?php

$int=0;
$rrd_list=array();
$rrd_filename=rrd_name($device['hostname'], array('app', $app['app_type'], $app['app_id'], $int));

if (!rrdtool_check_rrd_exists($rrd_filename)) {
    echo "file missing: $rrd_filename";
}

while (is_file($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'GPU '.$int,
        'ds'       => $rrdVar,
        'colour'   => $config['graph_colours']['manycolours'][$int]
    );

    $int++;
    $rrd_filename=rrd_name($device['hostname'], array('app', $app['app_type'], $app['app_id'], $int));
}

require 'includes/graphs/generic_multi_line_exact_numbers.inc.php';
