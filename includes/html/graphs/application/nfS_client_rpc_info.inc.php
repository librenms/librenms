<?php

// use the default if not set
if (! isset($vars['nfs_os'])) {
    $vars['nfs_os'] = '';
}

include 'includes/nfs-shared.inc.php';

$nfs_graph_name = 'client_rpc_info';
$name = 'nfs_' . $nfs_graph_name;
$app_id = $app['app_id'];
$unit_text = 'per second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 0;
$float_precision = 3;

// if we don't have a OS specific graph set, use the default
if (! isset($nfs_graphs[$nfs_graph_name][$vars['nfs_os']])) {
    $vars['nfs_os'] = '';
}

$rrd_list = [];
foreach ($nfs_graphs[$nfs_graph_name][$vars['nfs_os']] as $stat => $descr) {
    $filename = $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $stat]);
    if (Rrd::checkRrdExists($filename)) {
        $rrd_list[] = [
            'filename' => $filename,
            'descr' => $descr,
            'ds' => 'data',
        ];
    }
}

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

require 'includes/html/graphs/generic_multi_line.inc.php';
