<?php

$name = 'suricata';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $ftp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___ftp__memuse']);
    $ftp__memcap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___ftp__memcap']);
} else {
    $ftp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___ftp__memuse']);
    $ftp__memcap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___ftp__memcap']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($ftp__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $ftp__memuse_rrd_filename,
        'descr' => 'FTP Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $ftp__memuse_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($ftp__memcap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $ftp__memcap_rrd_filename,
        'descr' => 'FTP Memcap',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $ftp__memcap_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
