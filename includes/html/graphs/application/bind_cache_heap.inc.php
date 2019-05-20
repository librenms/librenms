<?php
$name = 'bind';
$app_id = $app['app_id'];
$unit_text     = 'Heap Memory';
$colours       = 'psychedelic';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', 'bind', $app['app_id'], 'cache'));

$rrd_list=array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Total',
        'ds'       => 'chmt',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'In Use',
        'ds'       => 'chmiu',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Highest In Use',
        'ds'       => 'chhmiu',
    );
} else {
    d_echo('RRD "'.$rrd_filename.'" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
