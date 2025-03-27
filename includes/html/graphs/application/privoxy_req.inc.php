<?php

$name = 'privoxy';
$unit_text = 'Request Types';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'CONNECT',
        'ds' => 'req_connect',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DELETE',
        'ds' => 'req_delete',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'GET',
        'ds' => 'req_get',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'HEAD',
        'ds' => 'req_head',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'OPTIONS',
        'ds' => 'req_options',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'PATCH',
        'ds' => 'req_patch',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'POST',
        'ds' => 'req_post',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'PUT',
        'ds' => 'req_put',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TRACE',
        'ds' => 'req_trace',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
