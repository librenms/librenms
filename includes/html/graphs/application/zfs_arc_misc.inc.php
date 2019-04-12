<?php
$name = 'zfs';
$app_id = $app['app_id'];
$unit_text     = 'per second';
$colours       = 'psychedelic';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app['app_id']));


$rrd_list=array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Deleted',
        'ds'       => 'deleted',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Recycle Misses',
        'ds'       => 'recycle_miss',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Evict Skip',
        'ds'       => 'evict_skip',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Mutex Skip',
        'ds'       => 'mutex_skip',
    );
} else {
    d_echo('RRD "'.$rrd_filename.'" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
