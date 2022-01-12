<?php

require 'includes/html/graphs/common.inc.php';
$name = 'supervisord';
$app_id = $app['app_id'];
$scale_min = 0;
$unit_text = 'Process';

$rrdVar = 'uptime';

$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$unitlen = 20;
$bigdescrlen = 25;
$smalldescrlen = 25;

$processes = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'supervisord');

$int = 0;
while (isset($processes[$int])) {
    $process_name = $processes[$int];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $process_name]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => $process_name,
            'ds'       => $rrdVar,
        ];
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
