<?php

$name = 'smart';
$app_id = $app['app_id'];
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

if (isset($vars['disk'])) {
    $disks = [$vars['disk']];
} else {
    $disks = Rrd::getRrdApplicationArrays($device, $app_id, $name);
}

$smart_enhancements = ['id9'];

$int = 0;
$rrd_list = [];
while (isset($disks[$int])) {
    $disk = $disks[$int];

    if (in_array($rrdVar, $smart_enhancements)) {
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name . '_' . $rrdVar, $app_id, $disk]);
    } else {
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $disk]);
    }

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => $disk,
            'ds'       => $rrdVar,
        ];
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
