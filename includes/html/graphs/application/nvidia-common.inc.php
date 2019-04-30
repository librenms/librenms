<?php
$name          = 'nvidia';
$app_id        = $app['app_id'];
$colours       = 'greens';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;
$app_id        = $app['app_id'];

$int=0;
$rrd_list=array();
$rrd_filename=rrd_name($device['hostname'], array('app', $app['app_type'], $app['app_id'], $int));

if (!rrdtool_check_rrd_exists($rrd_filename)) {
    echo "file missing: $rrd_filename";
}

while (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'GPU '.$int,
        'ds'       => $rrdVar,
    );

    $int++;
    $rrd_filename=rrd_name($device['hostname'], array('app', $app['app_type'], $app['app_id'], $int));
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
