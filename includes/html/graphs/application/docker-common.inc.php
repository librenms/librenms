<?php

$name = 'docker';
$app_id = $app['app_id'];
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$unitlen = 20;
$bigdescrlen = 25;
$smalldescrlen = 25;

if (isset($vars['container'])) {
    $containers = [$vars['container']];
} else {
    $containers = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'docker');
}

$int = 0;
while (isset($containers[$int])) {
    $container_name = $containers[$int];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $container_name]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => $container_name,
            'ds'       => $rrdVar,
        ];
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
