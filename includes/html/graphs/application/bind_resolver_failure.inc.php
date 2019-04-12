<?php
$name = 'bind';
$app_id = $app['app_id'];
$unit_text     = 'per second';
$colours       = 'psychedelic';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', 'bind', $app['app_id'], 'resolver'));


$rrd_list=array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'NXDOMAIN',
        'ds'       => 'nr',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'SERVFAIL',
        'ds'       => 'sr',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'FORMERR',
        'ds'       => 'fr',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'REFUSED',
        'ds'       => 'rr',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'EDNS(0) qry fl',
        'ds'       => 'eqf',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Trnctd Rcvd',
        'ds'       => 'trr',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Retry',
        'ds'       => 'qr',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Timeout',
        'ds'       => 'qt',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Lame Dele.',
        'ds'       => 'ldr',
    );
} else {
    d_echo('RRD "'.$rrd_filename.'" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
