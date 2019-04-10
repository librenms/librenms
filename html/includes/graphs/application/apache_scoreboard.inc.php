<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('app', 'apache', $app['app_id']));

$array = array(
    'sb_reading'   => array(
        'descr'  => 'Reading',
        'colour' => '750F7DFF',
    ),
    'sb_writing'   => array(
        'descr'  => 'Writing',
        'colour' => '00FF00FF',
    ),
    'sb_wait'      => array(
        'descr'  => 'Waiting',
        'colour' => '4444FFFF',
    ),
    'sb_start'     => array(
        'descr'  => 'Starting',
        'colour' => '157419FF',
    ),
    'sb_keepalive' => array(
        'descr'  => 'Keepalive',
        'colour' => 'FF0000FF',
    ),
    'sb_dns'       => array(
        'descr'  => 'DNS',
        'colour' => '6DC8FEFF',
    ),
    'sb_closing'   => array(
        'descr'  => 'Closing',
        'colour' => 'FFAB00FF',
    ),
    'sb_logging'   => array(
        'descr'  => 'Logging',
        'colour' => 'FFFF00FF',
    ),
    'sb_graceful'  => array(
        'descr'  => 'Graceful',
        'colour' => 'FF5576FF',
    ),
    'sb_idle'      => array(
        'descr'  => 'Idle',
        'colour' => 'FF4105FF',
    ),
);

$i = 0;
if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $file";
}

$colours   = 'mixed';
$nototal   = 1;
$unit_text = 'Workers';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
