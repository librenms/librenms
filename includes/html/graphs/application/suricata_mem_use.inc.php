<?php

$name = 'suricata';
$app_id = $app['app_id'];
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['instance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], $vars['instance']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id']]);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Flow',
        'ds'       => 'f_memuse',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'FTP',
        'ds'       => 'ftp_memuse',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'HTTP',
        'ds'       => 'http_memuse',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TCP',
        'ds'       => 'tcp_memuse',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TCP Reass',
        'ds'       => 'tcp_reass_memuse',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';

