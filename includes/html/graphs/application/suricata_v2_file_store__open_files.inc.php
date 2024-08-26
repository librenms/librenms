<?php

$name = 'suricata';
$unit_text = 'Open Files';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $file_store__open_files_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___file_store__open_files']);
} else {
    $file_store__open_files_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___file_store__open_files']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($file_store__open_files_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $file_store__open_files_rrd_filename,
        'descr' => 'File Store',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $file_store__open_files_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
