<?php

$name = 'suricata';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $http__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___http__memuse']);
    $http__memcap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___http__memcap']);
} else {
    $http__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___http__memuse']);
    $http__memcap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___http__memcap']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($http__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $http__memuse_rrd_filename,
        'descr' => 'HTTP Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $http__memuse_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($http__memcap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $http__memcap_rrd_filename,
        'descr' => 'HTTP Memcap',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $http__memcap_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
