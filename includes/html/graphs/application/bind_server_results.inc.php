<?php
$name = 'bind';
$app_id = $app['app_id'];
$unit_text     = 'per second';
$colours       = 'psychedelic';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', 'bind', $app['app_id'], 'server'));

$rrd_list=array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Successful',
        'ds'       => 'qrisa',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Authoritative',
        'ds'       => 'qriaa',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Non Authoritative',
        'ds'       => 'qrinaa',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'NX RR Set',
        'ds'       => 'qrin',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'SERVFAIL',
        'ds'       => 'qris',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'NXDOMAIN',
        'ds'       => 'qrind',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'With EDNS(0)',
        'ds'       => 'rwes',
    );
        $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Caused Rec',
        'ds'       => 'qcr',
    );
} else {
    d_echo('RRD "'.$rrd_filename.'" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
