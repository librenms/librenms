<?php

$name = 'logsize';
$app_id = $app['app_id'];
$unit_text = 'Bytes';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 0;
$float_precision = 3;

$log_files_sizes = $app->data['sets'][$vars['log_set']]['log_sizes'] ?? [];

$log_files = array_slice(array_keys($log_files_sizes), 0, 12);

$rrd_list = [];
foreach ($log_files as $index => $log_file) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], $vars['log_set'] . '_____-_____' . $log_file]);
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $log_file,
        'ds' => 'size',
    ];
}

if (sizeof($rrd_list)) {
    d_echo('No relevant log file RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
