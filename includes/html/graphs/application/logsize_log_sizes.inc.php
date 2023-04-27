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

$log_files = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'logsize', $vars['log_set'].'_____-_____');

$rrd_list = [];
foreach ($log_files as $index => $log_file) {
    $label = preg_filter('/^.*\_\_\_\_\_\-\_\_\_\_\_/', '', $log_file);
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], $log_file]);
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => $label,
        'ds'       => 'size',
    ];
}

if (sizeof($rrd_list)) {
    d_echo('No relevant log file RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
