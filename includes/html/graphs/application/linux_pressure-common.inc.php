<?php

// Expects $pressure_lines to be set by the including graph, a list of the lines to draw.
// Each item is an array with the keys resource, stall_type, ds, and descr.

$name = 'linux_pressure';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;
$descr_len = 16;

$rrd_list = [];
foreach ($pressure_lines as $pressure_line) {
    $rrd_filename = Rrd::name($device['hostname'], [
        'app',
        $name,
        $app->app_id,
        'system___' . $pressure_line['resource'] . '___' . $pressure_line['stall_type'],
    ]);

    // not every kernel reports every resource and stall type
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $pressure_line['descr'],
            'ds' => $pressure_line['ds'],
        ];
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
