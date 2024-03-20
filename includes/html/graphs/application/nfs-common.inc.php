<?php

// use the default if not set
if (! isset($vars['nfs_os'])) {
    $vars['nfs_os'] = '';
}

include 'includes/nfs-shared.inc.php';

$name = 'nfs';
$app_id = $app['app_id'];
if (! isset($unit_text)) {
    $unit_text = 'per second';
}
if (! isset($colours)) {
    if (isset($nfs_graphs_colours[$nfs_graph_name])) {
        $colours = $nfs_graphs_colours[$nfs_graph_name];
    } else {
        $colours = 'psychedelic';
    }
}
if (! isset($dostack)) {
    $dostack = 0;
}
if (! isset($printtotal)) {
    $printtotal = 1;
}
if (! isset($addarea)) {
    $addarea = 0;
}
$transparency = 0;
if (! isset($float_precision)) {
    $float_precision = 3;
}

// if we don't have a OS specific graph set, use the default
if (! isset($nfs_graphs[$nfs_graph_name][$vars['nfs_os']])) {
    $vars['nfs_os'] = '';
}

if (isset($start_stat) && isset($end_stat)) {
    $stat_set = [];
    $stat_keys_int = $start_stat;
    $stat_keys = array_keys($nfs_graphs[$nfs_graph_name][$vars['nfs_os']]);
    sort($stat_keys);
    while (isset($stat_keys[$stat_keys_int]) && $stat_keys_int <= $end_stat) {
        $stat_set[$stat_keys[$stat_keys_int]] = $nfs_graphs[$nfs_graph_name][$vars['nfs_os']][$stat_keys[$stat_keys_int]];
        $stat_keys_int++;
    }
} else {
    $stat_set = $nfs_graphs[$nfs_graph_name][$vars['nfs_os']];
}

$rrd_list = [];
foreach ($stat_set as $stat => $descr) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $stat]);
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
