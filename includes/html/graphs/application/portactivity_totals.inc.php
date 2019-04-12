<?php
$name = 'portactivity';
$app_id = $app['app_id'];
$unit_text     = 'Connections';
$colours       = 'psychedelic';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app['app_id'], $vars['port']));


$rrd_list=array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Total',
        'ds'       => 'total_conns',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'From',
        'ds'       => 'total_from',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'To',
        'ds'       => 'total_to',
    );
} else {
    d_echo('RRD "'.$rrd_filename.'" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
