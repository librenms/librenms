<?php

$name = 'seafile';
$app_id = $app['app_id'];
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

if (isset($vars['array'])) {
    $arrays = [$vars['array']];
} else {
    $arrays = Rrd::getRrdApplicationArrays($device, $app_id, $name, $category);
}

$int = 0;
$rrd_list = [];
while (isset($arrays[$int])) {
    $array = $arrays[$int];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $array]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => str_replace($category . '-', '', $array),
            'ds'       => $rrdVar,
        ];
    }
    $int++;
}

if ($unit_type == 'float') {
    require 'includes/html/graphs/generic_multi_line.inc.php';
} else {
    require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
}
