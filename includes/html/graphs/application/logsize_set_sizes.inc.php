<?php

$name = 'logsize';
$app_id = $app['app_id'];
$unit_text = 'Bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 0;
$float_precision = 3;

$log_sets = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'logsize');

$rrd_list = [];
foreach ($log_sets as $index => $log_set) {
    if (! preg_match('/\_\_\_\_\_\-\_\_\_\_\_/', $log_set)) {
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], $log_set]);
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $log_set,
            'ds' => 'size',
        ];
    }
}

if (sizeof($rrd_list)) {
    d_echo('No relevant log set RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
