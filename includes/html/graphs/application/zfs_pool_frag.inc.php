<?php
$name = 'zfs';
$app_id = $app['app_id'];
$unit_text     = 'percent';
$colours       = 'psychedelic';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app['app_id'], $vars['pool']));


$rrd_list=array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Frag',
        'ds'       => 'frag',
    );
} else {
    d_echo('RRD "'.$rrd_filename.'" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
