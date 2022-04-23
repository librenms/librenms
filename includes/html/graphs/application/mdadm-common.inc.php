<?php

$name = 'mdadm';
$app_id = $app['app_id'];
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

if (isset($vars['array'])) {
    $arrays = [$vars['array']];
} else {
    $arrays = Rrd::getRrdApplicationArrays($device, $app_id, $name);
}

$int = 0;
$rrd_list = [];
while (isset($arrays[$int])) {
    $array = $arrays[$int];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $array]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => $array,
            'ds'       => $rrdVar,
        ];
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
