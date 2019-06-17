<?php
$name = 'raid-drp';
$app_id = $app['app_id'];
$unit_text     = 'status';
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
        'descr'    => 'Good',
        'ds'       => 'bbu_good',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Bad',
        'ds'       => 'bbu_failed',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Charging',
        'ds'       => 'bbu_charging',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Not Present',
        'ds'       => 'bbu_notPresent',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'N/A',
        'ds'       => 'bbu_na',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Unknown',
        'ds'       => 'bbu_unknown',
    );
} else {
    d_echo('RRD "'.$rrd_filename.'" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
